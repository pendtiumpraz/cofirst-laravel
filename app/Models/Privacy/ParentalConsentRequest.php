<?php

namespace App\Models\Privacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * A request to a parent/guardian to approve processing of a child's data
 * (UU 27/2022 Pasal 25 ayat (2); PP 33/2026 Pasal 38 ayat (2)-(4)).
 *
 * The guardian receives a signed link (valid until expires_at, 7 days by default) to the
 * address the child entered; opening it and deciding is the verification by e-mail. Only
 * the SHA-256 hash of the link token is stored.
 *
 * @property int $id
 * @property int|null $child_user_id
 * @property string|null $guardian_name
 * @property string $guardian_email
 * @property string|null $guardian_relationship ayah|ibu|wali
 * @property int|null $guardian_user_id
 * @property string $status pending|granted|declined|expired|cancelled
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property int $send_count
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property array|null $granted_purposes
 */
class ParentalConsentRequest extends Model
{
    /** Days a guardian link stays valid. */
    public const VALID_DAYS = 7;

    /** Maximum number of e-mails per request (first send + resends). */
    public const MAX_SENDS = 5;

    public const STATUSES = [
        'pending' => 'Menunggu orang tua/wali',
        'granted' => 'Disetujui',
        'declined' => 'Ditolak',
        'expired' => 'Kedaluwarsa',
        'cancelled' => 'Dibatalkan',
    ];

    public const RELATIONSHIPS = [
        'ibu' => 'Ibu',
        'ayah' => 'Ayah',
        'wali' => 'Wali',
    ];

    protected $fillable = [
        'child_user_id',
        'guardian_name',
        'guardian_email',
        'guardian_relationship',
        'guardian_user_id',
        'token_hash',
        'status',
        'expires_at',
        'sent_at',
        'send_count',
        'responded_at',
        'response_ip',
        'response_user_agent',
        'granted_purposes',
        'decline_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'granted_purposes' => 'array',
        'send_count' => 'integer',
    ];

    protected $hidden = ['token_hash'];

    /** The child account waiting for approval. */
    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_user_id')->withTrashed();
    }

    /** The parent account linked after approval, when one exists. */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    /** Requests still waiting and not expired. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    /** Pending requests whose link has expired. */
    public function scopeStale(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('expires_at', '<=', now());
    }

    /**
     * Find a request by the raw token of its link.
     *
     * Args:
     *     token (string): Token from the URL.
     *
     * Returns:
     *     ParentalConsentRequest|null: The request, or null when unknown.
     */
    public static function findByToken(string $token): ?self
    {
        if (strlen($token) < 32 || strlen($token) > 128) {
            return null;
        }

        return static::query()->where('token_hash', hash('sha256', $token))->first();
    }

    /**
     * The open request of a child account, if any (used by login/status pages).
     *
     * Args:
     *     user (User): The child account.
     *
     * Returns:
     *     ParentalConsentRequest|null: Latest pending request.
     */
    public static function pendingFor(User $user): ?self
    {
        return static::query()->where('child_user_id', $user->id)->where('status', 'pending')->latest('id')->first();
    }

    /**
     * Replace the link token; returns the raw token (only ever held in memory).
     */
    public function issueToken(): string
    {
        $token = Str::random(64);
        $this->token_hash = hash('sha256', $token);

        return $token;
    }

    /**
     * Signed guardian link for a raw token, valid until expires_at.
     *
     * Args:
     *     token (string): Raw token returned by issueToken().
     *
     * Returns:
     *     string: Absolute signed URL.
     */
    public function signedUrl(string $token): string
    {
        return URL::temporarySignedRoute('privacy.parental-consent.show', $this->expires_at, ['token' => $token]);
    }

    /** Whether the guardian can still decide. */
    public function isOpen(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    /** Status, treating an expired pending request as expired. */
    public function getEffectiveStatusAttribute(): string
    {
        return $this->status === 'pending' && $this->expires_at->isPast() ? 'expired' : $this->status;
    }

    /** Indonesian label of the effective status. */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->effective_status] ?? $this->effective_status;
    }

    /** Guardian e-mail with the local part masked ("b***@gmail.com"). */
    public function getMaskedEmailAttribute(): string
    {
        return self::mask($this->guardian_email);
    }

    /**
     * Mask an e-mail address for display.
     *
     * Args:
     *     email (string|null): Address.
     *
     * Returns:
     *     string: Masked address.
     */
    public static function mask(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) {
            return '—';
        }

        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1).str_repeat('•', max(2, min(6, mb_strlen($local) - 1))).'@'.$domain;
    }
}
