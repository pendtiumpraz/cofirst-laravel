<?php

namespace App\Services\Privacy;

use App\Mail\Privacy\ParentalConsentMail;
use App\Models\Enrollment;
use App\Models\Privacy\ParentalConsentRequest;
use App\Models\User;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Parent/guardian consent for children (UU 27/2022 Pasal 25; PP 33/2026 Pasal 38).
 *
 * Flow: registration of a child creates an inactive account and a request ->
 * the guardian gets a signed link (7 days) -> grant: consents + policy acceptances are
 * recorded with method=parental, the account is activated and linked to an existing parent
 * account with the same e-mail -> decline / expiry: the pending account is anonymised and
 * archived and its pending enrollments are cancelled (data minimisation, Pasal 16 ayat (2)).
 */
class ParentalConsentService
{
    public function __construct(
        private readonly ConsentService $consents,
        private readonly PolicyService $policies,
    ) {
    }

    /**
     * Create the request for a child account (inside the registration transaction).
     *
     * Args:
     *     child (User): The new, inactive child account.
     *     guardianName (string): Name entered by the child.
     *     guardianEmail (string): Guardian e-mail entered by the child.
     *     relationship (string|null): ibu|ayah|wali.
     *
     * Returns:
     *     ParentalConsentRequest: Saved request (send it with send() after commit).
     */
    public function create(User $child, string $guardianName, string $guardianEmail, ?string $relationship = null): ParentalConsentRequest
    {
        $request = new ParentalConsentRequest([
            'child_user_id' => $child->id,
            'guardian_name' => $guardianName,
            'guardian_email' => Str::lower(trim($guardianEmail)),
            'guardian_relationship' => $relationship,
            'status' => 'pending',
            'expires_at' => now()->addDays(ParentalConsentRequest::VALID_DAYS),
        ]);
        $request->issueToken();
        $request->save();

        return $request;
    }

    /**
     * (Re)send the guardian e-mail with a fresh link; old links stop working.
     *
     * Args:
     *     request (ParentalConsentRequest): An open request.
     *
     * Returns:
     *     bool: True when the e-mail was handed to the mailer.
     */
    public function send(ParentalConsentRequest $request): bool
    {
        if ($request->status !== 'pending' || $request->send_count >= ParentalConsentRequest::MAX_SENDS) {
            return false;
        }

        $token = $request->issueToken();
        $request->expires_at = now()->addDays(ParentalConsentRequest::VALID_DAYS);
        $request->sent_at = now();
        $request->send_count++;
        $request->save();

        try {
            Mail::to($request->guardian_email, $request->guardian_name)
                ->send(new ParentalConsentMail('request', $request, $request->signedUrl($token)));

            return true;
        } catch (Throwable $e) {
            // Reason: never log the addresses themselves (personal data); the id is enough.
            Log::warning('Parental consent e-mail failed', ['request_id' => $request->id, 'error' => class_basename($e)]);

            return false;
        }
    }

    /**
     * The guardian approves: record consents and acceptances, activate and link the child.
     *
     * Args:
     *     request (ParentalConsentRequest): Open request.
     *     guardianName (string): Name confirmed by the guardian.
     *     optionalKeys (array<int, string>): Optional purposes the guardian ticked.
     *     http (Request): The guardian's request (IP / user agent evidence).
     *
     * Raises:
     *     DomainException: The request is no longer open.
     */
    public function grant(ParentalConsentRequest $request, string $guardianName, array $optionalKeys, Request $http): void
    {
        DB::transaction(function () use ($request, $guardianName, $optionalKeys, $http) {
            $request = ParentalConsentRequest::query()->lockForUpdate()->findOrFail($request->id);
            $this->assertOpen($request);

            $child = $request->child;
            if (! $child || $child->trashed()) {
                throw new DomainException('Akun anak tidak ditemukan.');
            }

            $guardian = User::query()->where('email', $request->guardian_email)->first();
            $guardian = $guardian && $guardian->hasRole('parent') ? $guardian : null;
            $evidence = [
                'verification' => 'email_link',
                'guardian_declaration' => true,
                'relationship' => $request->guardian_relationship,
                'request_id' => $request->id,
            ];

            $granted = [];
            foreach ($this->consents->purposesFor($child, true) as $purpose) {
                $wanted = $purpose->is_required || in_array($purpose->key, $optionalKeys, true);
                if (! $wanted || $purpose->isCookie()) {
                    continue;
                }
                $this->consents->grant($child, $purpose->key, 'parental', $guardian, $evidence, $http, $request->guardian_email, $guardianName);
                $granted[] = $purpose->key;
            }

            $this->policies->accept(
                $child,
                $this->policies->currentMany(['privacy_notice', 'terms', 'children_notice']),
                'parental',
                $http,
                $guardian,
                $request->guardian_email,
            );

            $child->forceFill(['is_active' => true])->save();

            if ($guardian) {
                $guardian->children()->syncWithoutDetaching([$child->id]);
            }

            $request->forceFill([
                'status' => 'granted',
                'guardian_name' => $guardianName,
                'guardian_user_id' => $guardian?->id,
                'granted_purposes' => $granted,
                'responded_at' => now(),
            ] + $this->responseMeta($http))->save();

            ConsentService::audit('parental_consent.granted', $child, ['request_id' => $request->id, 'purposes' => $granted]);
        });

        $this->notify($request->fresh(['child']), 'granted');
    }

    /**
     * The guardian declines: the pending account is anonymised and archived.
     *
     * Args:
     *     request (ParentalConsentRequest): Open request.
     *     http (Request): The guardian's request.
     *     reason (string|null): Optional reason given by the guardian.
     *
     * Raises:
     *     DomainException: The request is no longer open.
     */
    public function decline(ParentalConsentRequest $request, Request $http, ?string $reason = null): void
    {
        $childEmail = $request->child?->email;
        $childName = $request->child?->name;

        DB::transaction(function () use ($request, $http, $reason) {
            $request = ParentalConsentRequest::query()->lockForUpdate()->findOrFail($request->id);
            $this->assertOpen($request);

            $request->forceFill([
                'status' => 'declined',
                'decline_reason' => $reason ? mb_substr($reason, 0, 500) : null,
                'responded_at' => now(),
            ] + $this->responseMeta($http))->save();

            $this->discardPendingChild($request);
            ConsentService::audit('parental_consent.declined', $request, ['request_id' => $request->id]);
        });

        // Reason: PP 33/2026 Pasal 87 — tell the person that their data was erased.
        if ($childEmail) {
            $this->mailQuietly($childEmail, new ParentalConsentMail('declined', $request->fresh(), null, $childName));
        }
    }

    /**
     * Expire pending requests past their deadline and discard the waiting accounts.
     *
     * Returns:
     *     int: Number of requests expired.
     */
    public function expireStale(): int
    {
        $count = 0;

        ParentalConsentRequest::query()->stale()->with('child')->chunkById(100, function ($requests) use (&$count) {
            foreach ($requests as $request) {
                DB::transaction(function () use ($request) {
                    $request->forceFill(['status' => 'expired'])->save();
                    $this->discardPendingChild($request);
                });
                $count++;
            }
        });

        if ($count > 0) {
            ConsentService::audit('parental_consent.expired', null, ['count' => $count]);
        }

        return $count;
    }

    /**
     * Link a newly registered parent to children whose guardian used the invitation link.
     *
     * Args:
     *     parent (User): New parent account.
     *     token (string|null): Raw token from the guardian link (?undangan=…).
     *
     * Returns:
     *     User|null: The linked child, or null when nothing matched.
     */
    public function linkInvitedParent(User $parent, ?string $token): ?User
    {
        $request = $token ? ParentalConsentRequest::findByToken($token) : null;

        // Reason: the token proves control of the guardian mailbox; the e-mail must match too.
        if (! $request || $request->status !== 'granted' || $request->guardian_user_id
            || Str::lower($parent->email) !== $request->guardian_email || ! $request->child) {
            return null;
        }

        $parent->children()->syncWithoutDetaching([$request->child->id]);
        $request->forceFill(['guardian_user_id' => $parent->id])->save();

        return $request->child;
    }

    /** Reject a request that can no longer be answered. */
    private function assertOpen(ParentalConsentRequest $request): void
    {
        if (! $request->isOpen()) {
            throw new DomainException('Permintaan persetujuan ini sudah tidak berlaku.');
        }
    }

    /**
     * Anonymise and archive a child account that never got guardian consent.
     *
     * Reason: without consent there is no basis to keep the child's data (Pasal 25 & 43);
     * the guardian's name is dropped and the address kept masked as minimal evidence.
     */
    private function discardPendingChild(ParentalConsentRequest $request): void
    {
        $child = $request->child;

        if ($child && ! $child->trashed() && ! $child->is_active) {
            Enrollment::query()->where('student_id', $child->id)
                ->where('status', Enrollment::STATUS_PENDING)
                ->update(['status' => Enrollment::STATUS_CANCELLED, 'is_active' => false]);

            $child->profile()?->delete();
            $child->forceFill([
                'name' => 'Pendaftar dihapus',
                'email' => 'dihapus+'.$child->id.'.'.Str::lower(Str::random(6)).'@users.invalid',
                'remember_token' => null,
            ])->save();
            $child->delete();
        }

        $request->forceFill([
            'guardian_name' => null,
            'guardian_email' => ParentalConsentRequest::mask($request->guardian_email),
        ])->save();
    }

    /** Send confirmation e-mails after a decision. */
    private function notify(ParentalConsentRequest $request, string $outcome): void
    {
        if ($outcome !== 'granted' || ! $request->child) {
            return;
        }

        $this->mailQuietly($request->guardian_email, new ParentalConsentMail('granted_guardian', $request));
        $this->mailQuietly($request->child->email, new ParentalConsentMail('granted_child', $request));
    }

    /** Send without letting a mail failure undo a recorded decision. */
    private function mailQuietly(string $to, ParentalConsentMail $mail): void
    {
        try {
            Mail::to($to)->send($mail);
        } catch (Throwable $e) {
            Log::warning('Parental consent notification failed', ['kind' => $mail->kind, 'error' => class_basename($e)]);
        }
    }

    /** IP / user agent of the guardian's decision. */
    private function responseMeta(Request $http): array
    {
        $meta = ConsentService::requestMeta($http);

        return ['response_ip' => $meta['ip_address'], 'response_user_agent' => $meta['user_agent']];
    }
}
