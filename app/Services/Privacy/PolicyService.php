<?php

namespace App\Services\Privacy;

use App\Models\Privacy\PolicyAcceptance;
use App\Models\Privacy\PolicyDocument;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Versioned policy documents (UU 27/2022 Pasal 5, 21; PP 33/2026 Pasal 61-65).
 *
 * - current()/upcoming()/history(): which version is in force or scheduled.
 * - render(): safe Markdown (raw HTML escaped, unsafe links dropped) with {{variables}}
 *   resolved from Settings, so controller identity, DPO contact and retention periods in
 *   the texts always match Admin › Pengaturan › Privasi.
 * - pendingAcceptances()/accept(): re-acceptance after a new privacy notice / terms version.
 * - Version diffs for the admin live in PolicyDiff.
 */
class PolicyService
{
    public const CACHE_KEY = 'privacy.policies.published.v1';

    /** Types every account must have accepted (when re-acceptance is required). */
    public const ACCEPTANCE_TYPES = ['privacy_notice', 'terms'];

    /** Bundled default texts (also seeded as version 1.0 by PrivacySeeder). */
    public const DEFAULT_FILES = [
        'privacy_notice' => 'kebijakan-privasi.md',
        'terms' => 'syarat-ketentuan.md',
        'cookie_policy' => 'kebijakan-cookie.md',
        'children_notice' => 'privasi-anak.md',
        'dpa_template' => 'perjanjian-pemrosesan-data.md',
    ];

    /**
     * Published versions of every type without bodies (cached; busted by model events).
     *
     * Returns:
     *     Collection<int, PolicyDocument>: Ordered by type, revision.
     */
    public function publishedMeta(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => PolicyDocument::query()
            ->published()
            ->select(['id', 'type', 'revision', 'version', 'title', 'summary', 'changelog', 'status', 'requires_reacceptance', 'effective_at', 'published_at'])
            ->orderBy('type')->orderBy('revision')
            ->get());
    }

    /** Forget the cached list (after publishing). */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Version of a type currently in force (full document), or null when none is published.
     *
     * Args:
     *     type (string): privacy_notice|terms|cookie_policy|children_notice|dpa_template.
     *
     * Returns:
     *     PolicyDocument|null: The current version.
     */
    public function current(string $type): ?PolicyDocument
    {
        $meta = $this->currentMeta($type);

        return $meta ? PolicyDocument::find($meta->id) : null;
    }

    /**
     * Current versions of several types (full documents), skipping types without one.
     *
     * Args:
     *     types (array<int, string>): Document types.
     *
     * Returns:
     *     Collection<int, PolicyDocument>
     */
    public function currentMany(array $types): Collection
    {
        $ids = collect($types)->map(fn ($t) => $this->currentMeta($t)?->id)->filter()->all();

        return $ids === [] ? collect() : PolicyDocument::query()->whereIn('id', $ids)->get();
    }

    /** Current version of a type from the cached list (no body). */
    public function currentMeta(string $type): ?PolicyDocument
    {
        return $this->publishedMeta()
            ->where('type', $type)
            ->filter(fn (PolicyDocument $d) => $d->effective_at === null || $d->effective_at->lte(now()))
            ->sortByDesc('revision')
            ->first();
    }

    /**
     * Published version of a type that takes effect in the future (announced in advance).
     */
    public function upcoming(string $type): ?PolicyDocument
    {
        return $this->publishedMeta()
            ->where('type', $type)
            ->filter(fn (PolicyDocument $d) => $d->effective_at !== null && $d->effective_at->isFuture())
            ->sortBy('effective_at')
            ->first();
    }

    /**
     * Published versions of a type, newest first (no bodies).
     */
    public function history(string $type): Collection
    {
        return $this->publishedMeta()->where('type', $type)->sortByDesc('revision')->values();
    }

    /**
     * Current version, or the bundled default text as an unsaved document.
     *
     * Reason: the notice must always be reachable (Pasal 21), even before an admin publishes.
     */
    public function currentOrDefault(string $type): ?PolicyDocument
    {
        if ($doc = $this->current($type)) {
            return $doc;
        }

        $body = $this->defaultBody($type);

        return $body === null ? null : new PolicyDocument([
            'type' => $type,
            'revision' => 0,
            'version' => '1.0',
            'title' => PolicyDocument::TYPES[$type] ?? $type,
            'body' => $body,
            'status' => 'published',
        ]);
    }

    /**
     * Bundled default Markdown of a type.
     *
     * Returns:
     *     string|null: Markdown, or null when there is no bundled text.
     */
    public function defaultBody(string $type): ?string
    {
        $file = self::DEFAULT_FILES[$type] ?? null;
        $path = $file ? database_path('seeders/privacy/'.$file) : null;

        return $path && is_file($path) ? (string) file_get_contents($path) : null;
    }

    /**
     * Placeholders usable in policy texts as {{name}}.
     *
     * Returns:
     *     array<string, string>: name => value.
     */
    public function variables(): array
    {
        $dpoEmail = (string) setting('privacy.dpo_email', '');
        $mailto = $dpoEmail !== '' ? 'mailto:'.$dpoEmail : url('/');
        $dsr = $this->firstRoute(['privacy.dsr.create', 'privacy.request.create', 'privacy.requests.create']) ?? $mailto;

        return [
            'site_name' => (string) setting('general.site_name', 'Coding First'),
            'legal_name' => (string) setting('general.legal_name', 'Coding First Indonesia'),
            'site_url' => url('/'),
            'controller_name' => (string) setting('privacy.controller_name', setting('general.legal_name')),
            'controller_address' => (string) setting('privacy.controller_address', setting('general.address')),
            'contact_email' => (string) setting('general.contact_email', ''),
            'contact_phone' => (string) (setting('general.contact_phone') ?: '—'),
            'dpo_name' => (string) setting('privacy.dpo_name', 'Petugas Pelindungan Data Pribadi'),
            'dpo_email' => $dpoEmail,
            'dpo_phone' => (string) (setting('privacy.dpo_phone') ?: '—'),
            'child_age_threshold' => (string) setting('privacy.child_age_threshold', 18),
            'dsr_sla_hours' => (string) setting('privacy.dsr_sla_hours', 72),
            'breach_notification_hours' => (string) setting('privacy.breach_notification_hours', 72),
            'retention_inactive_account_months' => (string) setting('privacy.retention_inactive_account_months', 24),
            'retention_audit_log_days' => (string) setting('privacy.retention_audit_log_days', 365),
            'retention_financial_years' => (string) setting('privacy.retention_financial_years', 10),
            'retention_chat_months' => (string) setting('privacy.retention_chat_months', 24),
            'retention_export_days' => (string) setting('privacy.retention_export_days', 7),
            'payment_due_days' => (string) setting('finance.payment_due_days', 7),
            'session_cookie' => (string) config('session.cookie'),
            'session_lifetime' => (string) setting('security.session_lifetime_minutes', config('session.lifetime')),
            'url_privacy' => $this->firstRoute(['privacy.notice']) ?? url('/'),
            'url_terms' => $this->firstRoute(['legal.terms']) ?? url('/'),
            'url_cookies' => $this->firstRoute(['privacy.cookies']) ?? url('/'),
            'url_children' => $this->firstRoute(['privacy.children']) ?? url('/'),
            'url_cookie_settings' => $this->firstRoute(['privacy.cookies.preferences']) ?? url('/'),
            'url_history' => $this->firstRoute(['privacy.history']) ?? url('/'),
            'url_privacy_centre' => $this->firstRoute(['account.privacy.index', 'privacy.index']) ?? $dsr,
            'url_dsr' => $dsr,
        ];
    }

    /** Names of the available variables (for the admin editor help). */
    public function variableNames(): array
    {
        return array_merge(array_keys($this->variables()), ['version', 'effective_date']);
    }

    /**
     * Render a document body to safe HTML.
     *
     * Args:
     *     document (PolicyDocument): Document to render.
     *
     * Returns:
     *     HtmlString: HTML with heading ids and scrollable tables.
     */
    public function render(PolicyDocument $document): HtmlString
    {
        $vars = $this->variables() + [
            'version' => (string) $document->version,
            'effective_date' => self::date($document->effective_at ?? $document->published_at ?? now()),
        ];

        $markdown = preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/', fn ($m) => $vars[$m[1]] ?? $m[0], (string) $document->body);

        // Reason: html_input=escape + allow_unsafe_links=false keeps admin-written Markdown from injecting scripts.
        $html = Str::markdown($markdown, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        $used = [];
        $html = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function ($m) use (&$used) {
            $slug = Str::slug(strip_tags(html_entity_decode($m[2]))) ?: 'bagian';
            $id = $slug;
            for ($i = 2; isset($used[$id]); $i++) {
                $id = $slug.'-'.$i;
            }
            $used[$id] = true;

            return '<h'.$m[1].' id="'.$id.'">'.$m[2].'</h'.$m[1].'>';
        }, $html);

        $html = str_replace(['<table>', '</table>'], ['<div class="overflow-x-auto"><table>', '</table></div>'], $html);

        return new HtmlString($html);
    }

    /**
     * Table of contents from rendered HTML (h2 only).
     *
     * Returns:
     *     array<int, array{id: string, label: string}>
     */
    public function toc(HtmlString $html): array
    {
        preg_match_all('/<h2 id="([^"]+)">(.*?)<\/h2>/s', (string) $html, $matches, PREG_SET_ORDER);

        return array_map(fn ($m) => ['id' => $m[1], 'label' => html_entity_decode(strip_tags($m[2]))], $matches);
    }

    /**
     * Publish a draft.
     *
     * Args:
     *     document (PolicyDocument): Draft to publish.
     *     effectiveAt (CarbonInterface|null): When it takes effect (null = now).
     *     requiresReacceptance (bool): Ask every account to accept it again.
     *     by (User): Publisher.
     */
    public function publish(PolicyDocument $document, ?CarbonInterface $effectiveAt, bool $requiresReacceptance, User $by): void
    {
        $document->forceFill([
            'status' => 'published',
            'published_at' => now(),
            'effective_at' => $effectiveAt ?? now(),
            'published_by' => $by->id,
            'requires_reacceptance' => $requiresReacceptance && in_array($document->type, self::ACCEPTANCE_TYPES, true),
        ])->save();

        self::flushCache();
        ConsentService::audit('policy.published', $document, ['type' => $document->type, 'version' => $document->version]);
    }

    /**
     * Documents the user still has to accept (new versions that require re-acceptance).
     *
     * Args:
     *     user (User): Signed-in account.
     *
     * Returns:
     *     Collection<int, PolicyDocument>: Current versions to accept (no bodies).
     */
    public function pendingAcceptances(User $user): Collection
    {
        $baselines = $this->baselines();

        if ($baselines === []) {
            return collect();
        }

        $meta = $this->publishedMeta()->keyBy('id');
        $accepted = PolicyAcceptance::query()->where('user_id', $user->id)
            ->whereIn('policy_type', array_keys($baselines))->pluck('policy_document_id');

        $pending = collect();
        foreach ($baselines as $type => $baseline) {
            $best = $accepted->map(fn ($id) => $meta->get($id))->filter(fn ($d) => $d && $d->type === $type)->max('revision') ?? 0;
            if ($best < $baseline->revision) {
                $pending->push($this->currentMeta($type));
            }
        }

        return $pending->filter()->values();
    }

    /**
     * Signature of the versions everyone must have accepted ('' when none).
     *
     * Reason: lets the middleware skip the acceptance query while nothing has changed.
     */
    public function baselineSignature(): string
    {
        return collect($this->baselines())->map(fn ($d) => $d->id)->implode(',');
    }

    /**
     * Record acceptance of documents.
     *
     * Args:
     *     user (User): Account the acceptance belongs to.
     *     documents (iterable<PolicyDocument>): Accepted versions (published only).
     *     context (string): registration|reacceptance|parental|admin.
     *     request (Request|null): IP / user agent evidence.
     *     actor (User|null): Account acting for the user (guardian/admin).
     *     actorEmail (string|null): Guardian e-mail without account.
     */
    public function accept(User $user, iterable $documents, string $context, ?Request $request = null, ?User $actor = null, ?string $actorEmail = null): void
    {
        $meta = ConsentService::requestMeta($request);

        DB::transaction(function () use ($user, $documents, $context, $actor, $actorEmail, $meta) {
            foreach ($documents as $doc) {
                if (! $doc instanceof PolicyDocument || ! $doc->exists || ! $doc->isPublished()) {
                    continue;
                }

                PolicyAcceptance::create([
                    'user_id' => $user->id,
                    'policy_document_id' => $doc->id,
                    'policy_type' => $doc->type,
                    'policy_version' => $doc->version,
                    'context' => $context,
                    'actor_user_id' => $actor?->id ?? ($context === 'parental' ? null : $user->id),
                    'actor_email' => $actorEmail,
                    'accepted_at' => now(),
                ] + $meta);
            }
        });
    }

    /**
     * Format a moment in the configured timezone with Indonesian month names.
     *
     * Args:
     *     at (CarbonInterface|null): Moment (stored in UTC).
     *     withTime (bool): Append "HH.mm WIB".
     *
     * Returns:
     *     string: e.g. "13 Oktober 2026" or "13 Oktober 2026, 14.02 WIB"; '—' when null.
     */
    public static function date(?CarbonInterface $at, bool $withTime = false): string
    {
        if (! $at) {
            return '—';
        }

        $tz = (string) setting('general.timezone', 'Asia/Jakarta');
        $local = Carbon::instance($at)->copy()->setTimezone($tz)->locale('id');
        $zone = ['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT'][$tz] ?? $tz;

        return $withTime ? $local->translatedFormat('j F Y, H.i').' '.$zone : $local->translatedFormat('j F Y');
    }

    /**
     * Per acceptance type, the latest in-force version that requires re-acceptance.
     *
     * @return array<string, PolicyDocument>
     */
    private function baselines(): array
    {
        $baselines = [];

        foreach (self::ACCEPTANCE_TYPES as $type) {
            $baseline = $this->publishedMeta()
                ->where('type', $type)
                ->filter(fn (PolicyDocument $d) => $d->requires_reacceptance && ($d->effective_at === null || $d->effective_at->lte(now())))
                ->sortByDesc('revision')
                ->first();

            if ($baseline) {
                $baselines[$type] = $baseline;
            }
        }

        return $baselines;
    }

    /** URL of the first existing parameterless route. */
    private function firstRoute(array $names): ?string
    {
        foreach ($names as $name) {
            if (Route::has($name)) {
                try {
                    return route($name);
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }
}
