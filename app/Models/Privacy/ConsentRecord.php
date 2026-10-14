<?php

namespace App\Models\Privacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One entry of the append-only consent ledger (UU 27/2022 Pasal 22 & 24; PP 33/2026 Pasal 34, 36).
 *
 * Rows are never updated or deleted: a withdrawal is a new row with decision `withdrawn`
 * (Pasal 9; PP 33/2026 Pasal 37, 90-92). The latest row per subject + purpose is the
 * current state. Create rows through App\Services\Privacy\ConsentService.
 *
 * @property int $id
 * @property int|null $subject_user_id
 * @property int|null $actor_user_id
 * @property string|null $actor_email guardian e-mail when the guardian has no account
 * @property string|null $actor_name
 * @property string|null $actor_role self|guardian|admin
 * @property string $purpose_key
 * @property int $purpose_version
 * @property string $decision granted|withdrawn|denied
 * @property string $method registration|banner|privacy_centre|parental|admin
 * @property array|null $evidence
 * @property \Illuminate\Support\Carbon $created_at
 */
class ConsentRecord extends Model
{
    public const UPDATED_AT = null;

    public const DECISIONS = [
        'granted' => 'Diberikan',
        'withdrawn' => 'Ditarik',
        'denied' => 'Ditolak',
    ];

    public const METHODS = [
        'registration' => 'Pendaftaran',
        'banner' => 'Banner cookie',
        'privacy_centre' => 'Pusat Privasi',
        'parental' => 'Orang tua/wali',
        'admin' => 'Dicatat admin',
    ];

    protected $fillable = [
        'subject_user_id',
        'actor_user_id',
        'actor_email',
        'actor_name',
        'actor_role',
        'consent_purpose_id',
        'purpose_key',
        'purpose_version',
        'decision',
        'method',
        'policy_document_id',
        'ip_address',
        'user_agent',
        'evidence',
        'created_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'purpose_version' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Enforce the append-only ledger at model level.
     *
     * Reason: proof of consent must be immutable (Pasal 24); corrections are new rows.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Catatan persetujuan tidak dapat diubah; tambahkan catatan baru.');
        });

        static::deleting(function () {
            throw new LogicException('Catatan persetujuan tidak dapat dihapus.');
        });
    }

    /** The person the consent is about. */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id')->withTrashed();
    }

    /** The account that acted (the subject, a linked parent or an admin). */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id')->withTrashed();
    }

    /** The purpose (may be null when the purpose was removed; purpose_key stays). */
    public function purpose(): BelongsTo
    {
        return $this->belongsTo(ConsentPurpose::class, 'consent_purpose_id');
    }

    /** Privacy notice version in force when the decision was made. */
    public function policyDocument(): BelongsTo
    {
        return $this->belongsTo(PolicyDocument::class);
    }

    /** Rows of one purpose. */
    public function scopeForPurpose(Builder $query, string $key): Builder
    {
        return $query->where('purpose_key', $key);
    }

    /** Whether this row grants consent. */
    public function isGranted(): bool
    {
        return $this->decision === 'granted';
    }

    /** Indonesian label of the decision. */
    public function getDecisionLabelAttribute(): string
    {
        return self::DECISIONS[$this->decision] ?? $this->decision;
    }

    /** Badge tone of the decision. */
    public function getDecisionToneAttribute(): string
    {
        return match ($this->decision) {
            'granted' => 'success',
            'withdrawn' => 'warning',
            default => 'neutral',
        };
    }

    /** Indonesian label of the method. */
    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    /** Who acted, for display ("Orang tua/wali: Budi", "Admin: Sari", "Diri sendiri"). */
    public function getActorLabelAttribute(): string
    {
        $name = $this->actor_display_name;

        return match ($this->actor_role) {
            'guardian' => 'Orang tua/wali'.($name ? ': '.$name : ''),
            'admin' => 'Admin'.($name ? ': '.$name : ''),
            default => 'Diri sendiri',
        };
    }

    /** Name (or e-mail) of whoever acted, null when the subject acted alone. */
    public function getActorDisplayNameAttribute(): ?string
    {
        if ($this->actor_role === 'self' || $this->actor_role === null) {
            return null;
        }

        return $this->actor?->name ?? $this->actor_name ?? $this->actor_email;
    }
}
