<?php

namespace App\Mail\Privacy;

use App\Models\Privacy\ParentalConsentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mails of the parental-consent flow (UU 27/2022 Pasal 25; PP 33/2026 Pasal 38, 87).
 *
 * Kinds:
 *   request           to the guardian: approve or decline, with the signed link
 *   granted_guardian  to the guardian: confirmation of the recorded decision
 *   granted_child     to the child: the account is active
 *   declined          to the child: the guardian declined, the registration data was erased
 */
class ParentalConsentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Args:
     *     kind (string): request|granted_guardian|granted_child|declined.
     *     consentRequest (ParentalConsentRequest): The request.
     *     url (string|null): Signed guardian link (kind=request).
     *     childName (string|null): Child name when the account was already anonymised.
     */
    public function __construct(
        public string $kind,
        public ParentalConsentRequest $consentRequest,
        public ?string $url = null,
        public ?string $childName = null,
    ) {
    }

    /**
     * Subject line per kind.
     */
    public function envelope(): Envelope
    {
        $site = (string) setting('general.site_name', 'Coding First');
        $child = $this->firstName();

        return new Envelope(subject: match ($this->kind) {
            'request' => "Mohon persetujuan Anda untuk akun {$child} di {$site}",
            'granted_guardian' => "Persetujuan Anda untuk {$child} sudah tercatat",
            'granted_child' => "Akunmu di {$site} sudah aktif",
            default => "Pendaftaranmu di {$site} tidak dilanjutkan",
        });
    }

    /**
     * Markdown view (Laravel mail theme; no custom colours).
     */
    public function content(): Content
    {
        return new Content(markdown: 'privacy.parental-consent-mail', with: [
            'kind' => $this->kind,
            'request' => $this->consentRequest,
            'url' => $this->url,
            'childFirstName' => $this->firstName(),
            'site' => (string) setting('general.site_name', 'Coding First'),
            'dpoEmail' => (string) setting('privacy.dpo_email', ''),
            'expires' => \App\Services\Privacy\PolicyService::date($this->consentRequest->expires_at, true),
        ]);
    }

    /** First name of the child (minimum necessary in an e-mail subject). */
    private function firstName(): string
    {
        $name = $this->childName ?? $this->consentRequest->child?->name ?? 'anak Anda';

        return (string) strtok(trim($name), ' ');
    }
}
