<?php

namespace App\Services\Privacy;

use App\Models\Privacy\ConsentPurpose;
use App\Models\Privacy\ConsentRecord;
use App\Models\User;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * Consent API for every module (UU 27/2022 Pasal 20-25; PP 33/2026 Pasal 32-38, 90-92).
 *
 * Usage from other modules:
 *     $consents = app(ConsentService::class);
 *     if ($consents->hasConsent($child, 'media_publication')) { … }          // gate optional processing
 *     $ids = $consents->subjectIdsWithConsent('leaderboard_public', $userIds); // bulk, no N+1
 *     $consents->grant($user, 'marketing_email', 'privacy_centre', actor: $user, request: $request);
 *     $consents->withdraw($user, 'marketing_email', 'privacy_centre', actor: $user, request: $request);
 *     $consents->purposesFor($user);  $consents->history($user);  $consents->currentStates($user);
 *
 * Rules: the ledger is append-only; a purpose whose legal basis is not `consent` always
 * counts as allowed; consent given for an older purpose version no longer counts
 * (re-consent after a material change, PP 33/2026 Pasal 67 ayat (4)); a child's consent
 * must come from a parent/guardian (Pasal 25 ayat (2)); withdrawal is always possible.
 */
class ConsentService
{
    public const METHODS = ['registration', 'banner', 'privacy_centre', 'parental', 'admin'];

    /** @var Collection<string, ConsentPurpose>|null */
    private ?Collection $purposeCache = null;

    /** @var array<string, ConsentRecord|null> latest record per "subject|purpose" */
    private array $latestCache = [];

    /**
     * All purposes keyed by key (active and inactive), cached for this request.
     *
     * Returns:
     *     Collection<string, ConsentPurpose>: Purposes by key.
     */
    public function purposes(): Collection
    {
        return $this->purposeCache ??= ConsentPurpose::query()->ordered()->get()->keyBy('key');
    }

    /**
     * One purpose by key.
     *
     * Args:
     *     key (string): Purpose key, e.g. 'media_publication'.
     *
     * Returns:
     *     ConsentPurpose|null: The purpose, or null when unknown.
     */
    public function purpose(string $key): ?ConsentPurpose
    {
        return $this->purposes()->get($key);
    }

    /**
     * Active purposes relevant for a user (by roles and age), or for a visitor when null.
     *
     * Args:
     *     user (User|null): Subject; null = anonymous visitor ('guest').
     *     consentOnly (bool): Only purposes people decide on (legal basis = consent).
     *
     * Returns:
     *     Collection<int, ConsentPurpose>: Ordered purposes.
     */
    public function purposesFor(?User $user, bool $consentOnly = false): Collection
    {
        $roles = $user ? $user->getRoleNames()->all() : ['guest'];
        $isChild = $user ? $this->isChild($user) : false;

        return $this->purposes()
            ->filter(fn (ConsentPurpose $p) => $p->is_active && $p->appliesTo($roles, $isChild))
            ->when($consentOnly, fn ($c) => $c->filter(fn (ConsentPurpose $p) => $p->isConsentBased()))
            ->values();
    }

    /**
     * Whether the subject currently consents to a purpose.
     *
     * Args:
     *     subject (User): The person the data is about.
     *     purposeKey (string): Purpose key.
     *
     * Returns:
     *     bool: True when processing is allowed. Unknown or inactive purposes fail closed.
     */
    public function hasConsent(User $subject, string $purposeKey): bool
    {
        $purpose = $this->purpose($purposeKey);

        if (! $purpose || ! $purpose->is_active) {
            return false;
        }

        if (! $purpose->isConsentBased()) {
            return true;
        }

        $latest = $this->latest($subject, $purposeKey);

        return $latest !== null && $latest->isGranted() && $latest->purpose_version >= $purpose->version;
    }

    /**
     * Ids of subjects that currently consent to a purpose (one query, for lists).
     *
     * Args:
     *     purposeKey (string): Purpose key.
     *     userIds (iterable<int>|null): Limit to these users; null = everyone.
     *
     * Returns:
     *     array<int, int>: Consenting user ids.
     */
    public function subjectIdsWithConsent(string $purposeKey, ?iterable $userIds = null): array
    {
        $purpose = $this->purpose($purposeKey);

        if (! $purpose || ! $purpose->is_active) {
            return [];
        }

        $ids = $userIds === null ? null : collect($userIds)->map(fn ($id) => (int) $id)->unique()->values()->all();

        if (! $purpose->isConsentBased()) {
            return $ids ?? User::query()->pluck('id')->all();
        }

        $latestIds = ConsentRecord::query()
            ->selectRaw('max(id) as id')
            ->where('purpose_key', $purposeKey)
            ->whereNotNull('subject_user_id')
            ->when($ids !== null, fn ($q) => $q->whereIn('subject_user_id', $ids))
            ->groupBy('subject_user_id');

        return ConsentRecord::query()
            ->whereIn('id', $latestIds)
            ->where('decision', 'granted')
            ->where('purpose_version', '>=', $purpose->version)
            ->pluck('subject_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Record that consent was given.
     *
     * Args:
     *     subject (User): The person the data is about.
     *     purposeKey (string): Purpose key.
     *     method (string): registration|banner|privacy_centre|parental|admin.
     *     actor (User|null): Account acting (subject, linked parent or admin); null for a guardian without account.
     *     evidence (array): Extra proof (form, declaration, references).
     *     request (Request|null): Source of IP / user agent.
     *     actorEmail (string|null): Guardian e-mail when the guardian has no account.
     *     actorName (string|null): Guardian name.
     *
     * Returns:
     *     ConsentRecord: The new ledger row.
     *
     * Raises:
     *     DomainException: A child's consent given without a guardian, or the purpose cannot be consented to.
     */
    public function grant(User $subject, string $purposeKey, string $method, ?User $actor = null, array $evidence = [], ?Request $request = null, ?string $actorEmail = null, ?string $actorName = null): ConsentRecord
    {
        $purpose = $this->requirePurpose($purposeKey);

        if (! $purpose->isConsentBased()) {
            throw new DomainException('Tujuan ini tidak memakai dasar persetujuan.');
        }

        $role = $this->actorRole($subject, $actor, $method);

        // Reason: Pasal 25 ayat (2) — a child cannot validly consent alone.
        if ($role === 'self' && $this->requiresGuardian($subject)) {
            throw new DomainException('Persetujuan untuk anak harus diberikan oleh orang tua/wali.');
        }

        return $this->record($subject, $purpose, 'granted', $method, $role, $actor, $evidence, $request, $actorEmail, $actorName);
    }

    /**
     * Record that consent was withdrawn (always allowed; Pasal 9).
     *
     * Args:
     *     subject (User): The person the data is about.
     *     purposeKey (string): Purpose key.
     *     method (string): How it was withdrawn.
     *     actor (User|null): Account acting.
     *     evidence (array): Extra proof.
     *     request (Request|null): Source of IP / user agent.
     *     actorEmail (string|null): Guardian e-mail without account.
     *     actorName (string|null): Guardian name.
     *
     * Returns:
     *     ConsentRecord: The new ledger row.
     */
    public function withdraw(User $subject, string $purposeKey, string $method, ?User $actor = null, array $evidence = [], ?Request $request = null, ?string $actorEmail = null, ?string $actorName = null): ConsentRecord
    {
        $purpose = $this->requirePurpose($purposeKey);

        if ($purpose->is_required && $purpose->category === 'essential') {
            // Reason: withdrawing a required consent ends the service; the DSR flow handles that
            // (erasure / account closure), the ledger still records the decision.
            $evidence['ends_service'] = true;
        }

        $role = $this->actorRole($subject, $actor, $method);

        return $this->record($subject, $purpose, 'withdrawn', $method, $role, $actor, $evidence, $request, $actorEmail, $actorName);
    }

    /**
     * Record that consent was refused when first asked (no earlier grant).
     *
     * Args:
     *     subject (User): The person the data is about.
     *     purposeKey (string): Purpose key.
     *     method (string): Where it was refused.
     *     actor (User|null): Account acting.
     *     evidence (array): Extra proof.
     *     request (Request|null): Source of IP / user agent.
     *
     * Returns:
     *     ConsentRecord: The new ledger row.
     */
    public function deny(User $subject, string $purposeKey, string $method, ?User $actor = null, array $evidence = [], ?Request $request = null): ConsentRecord
    {
        $purpose = $this->requirePurpose($purposeKey);
        $role = $this->actorRole($subject, $actor, $method);

        return $this->record($subject, $purpose, 'denied', $method, $role, $actor, $evidence, $request, null, null);
    }

    /**
     * Apply a set of decisions, writing rows only where the state changes.
     *
     * Args:
     *     subject (User): The person the data is about.
     *     decisions (array<string, bool>): purpose key => granted?
     *     method (string): How the decisions were made.
     *     actor (User|null): Account acting.
     *     request (Request|null): Source of IP / user agent.
     *     evidence (array): Extra proof added to every row.
     *
     * Returns:
     *     Collection<int, ConsentRecord>: New rows.
     */
    public function sync(User $subject, array $decisions, string $method, ?User $actor = null, ?Request $request = null, array $evidence = []): Collection
    {
        return DB::transaction(function () use ($subject, $decisions, $method, $actor, $request, $evidence) {
            $rows = collect();

            foreach ($decisions as $key => $granted) {
                $purpose = $this->purpose((string) $key);

                if (! $purpose || ! $purpose->isConsentBased()) {
                    continue;
                }

                $current = $this->hasConsent($subject, $purpose->key);

                if ($granted && ! $current) {
                    $rows->push($this->grant($subject, $purpose->key, $method, $actor, $evidence, $request));
                } elseif (! $granted && $current) {
                    $rows->push($this->withdraw($subject, $purpose->key, $method, $actor, $evidence, $request));
                }
            }

            return $rows;
        });
    }

    /**
     * Full consent history of a subject, newest first.
     *
     * Args:
     *     subject (User): The person the data is about.
     *
     * Returns:
     *     Collection<int, ConsentRecord>: Rows with purpose and actor loaded.
     */
    public function history(User $subject): Collection
    {
        return ConsentRecord::query()
            ->with(['purpose', 'actor:id,name'])
            ->where('subject_user_id', $subject->id)
            ->latest('id')
            ->get();
    }

    /**
     * Latest ledger row per purpose for a subject (two queries).
     *
     * Args:
     *     subject (User): The person the data is about.
     *
     * Returns:
     *     Collection<string, ConsentRecord>: purpose key => latest row.
     */
    public function currentStates(User $subject): Collection
    {
        $latestIds = ConsentRecord::query()
            ->selectRaw('max(id) as id')
            ->where('subject_user_id', $subject->id)
            ->groupBy('purpose_key');

        return ConsentRecord::query()->with('actor:id,name')->whereIn('id', $latestIds)->get()->keyBy('purpose_key');
    }

    /**
     * Whether the user counts as a child: younger than setting('privacy.child_age_threshold').
     *
     * Args:
     *     user (User): Any account.
     *
     * Returns:
     *     bool: True when the profile birth date shows a minor.
     */
    public function isChild(User $user): bool
    {
        $age = $user->profile?->birth_date?->age;

        return $age !== null && $age < $this->childAgeThreshold();
    }

    /**
     * Whether consent for this user must come from a parent/guardian.
     */
    public function requiresGuardian(User $user): bool
    {
        return (bool) setting('privacy.require_parental_consent', true) && $this->isChild($user);
    }

    /** Age (years) below which a person is a child; PP 33/2026 Pasal 1 angka 21 uses 18. */
    public function childAgeThreshold(): int
    {
        return (int) setting('privacy.child_age_threshold', 18);
    }

    /**
     * IP and user agent of a request, trimmed to column sizes.
     *
     * Args:
     *     request (Request|null): Current request.
     *
     * Returns:
     *     array{ip_address: string|null, user_agent: string|null}
     */
    public static function requestMeta(?Request $request): array
    {
        return [
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
        ];
    }

    /**
     * Write an audit-log entry when the governance module's logger exists.
     *
     * Reason: the AuditLogger belongs to another module and may not exist yet; logging
     * must never break the user's action, so failures are reported and swallowed.
     *
     * Args:
     *     action (string): Action key, e.g. 'consent.withdrawn'.
     *     subject (mixed): Model the action concerns.
     *     context (array): Non-sensitive context (ids, counts; never personal data payloads).
     */
    public static function audit(string $action, mixed $subject = null, array $context = []): void
    {
        $logger = 'App\\Services\\Privacy\\AuditLogger';

        if (! class_exists($logger)) {
            return;
        }

        try {
            $logger::log($action, $subject, $context);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Forget cached purposes/states (after admin edits or in long-running processes). */
    public function flush(): void
    {
        $this->purposeCache = null;
        $this->latestCache = [];
    }

    /** Latest ledger row of a subject for a purpose (memoised per request). */
    private function latest(User $subject, string $purposeKey): ?ConsentRecord
    {
        $cacheKey = $subject->id.'|'.$purposeKey;

        if (! array_key_exists($cacheKey, $this->latestCache)) {
            $this->latestCache[$cacheKey] = ConsentRecord::query()
                ->where('subject_user_id', $subject->id)
                ->where('purpose_key', $purposeKey)
                ->latest('id')
                ->first();
        }

        return $this->latestCache[$cacheKey];
    }

    /** Purpose by key or an exception. */
    private function requirePurpose(string $key): ConsentPurpose
    {
        $purpose = $this->purpose($key);

        if (! $purpose) {
            throw new InvalidArgumentException("Tujuan persetujuan [{$key}] tidak dikenal.");
        }

        return $purpose;
    }

    /**
     * Who is acting relative to the subject: self, guardian or admin.
     */
    private function actorRole(User $subject, ?User $actor, string $method): string
    {
        if (! in_array($method, self::METHODS, true)) {
            throw new InvalidArgumentException("Metode persetujuan [{$method}] tidak dikenal.");
        }

        if ($method === 'parental') {
            return 'guardian';
        }

        if ($method === 'admin') {
            return 'admin';
        }

        if ($actor && $actor->id !== $subject->id && $actor->isParentOf($subject)) {
            return 'guardian';
        }

        return 'self';
    }

    /**
     * Append one ledger row.
     */
    private function record(User $subject, ConsentPurpose $purpose, string $decision, string $method, string $role, ?User $actor, array $evidence, ?Request $request, ?string $actorEmail, ?string $actorName): ConsentRecord
    {
        $notice = class_exists(PolicyService::class) ? app(PolicyService::class)->current('privacy_notice') : null;

        $record = ConsentRecord::create([
            'subject_user_id' => $subject->id,
            'actor_user_id' => $actor?->id,
            'actor_email' => $actorEmail,
            'actor_name' => $actorName,
            'actor_role' => $role,
            'consent_purpose_id' => $purpose->id,
            'purpose_key' => $purpose->key,
            'purpose_version' => $purpose->version,
            'decision' => $decision,
            'method' => $method,
            'policy_document_id' => $notice?->id,
            'evidence' => array_merge(['text_hash' => $purpose->textHash(), 'notice_version' => $notice?->version], $evidence),
            'created_at' => now(),
        ] + self::requestMeta($request));

        $this->latestCache[$subject->id.'|'.$purpose->key] = $record;

        self::audit('consent.'.$decision, $subject, ['purpose' => $purpose->key, 'method' => $method, 'record_id' => $record->id]);

        return $record;
    }
}
