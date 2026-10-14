<?php

namespace App\Models\Privacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Evidence that an account (or a guardian on its behalf) accepted one version of a policy
 * document. Append-only, like the consent ledger.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $policy_document_id
 * @property string $policy_type
 * @property string $policy_version
 * @property string $context registration|reacceptance|parental|admin
 * @property \Illuminate\Support\Carbon $accepted_at
 */
class PolicyAcceptance extends Model
{
    public $timestamps = false;

    public const CONTEXTS = [
        'registration' => 'Saat pendaftaran',
        'reacceptance' => 'Persetujuan ulang',
        'parental' => 'Oleh orang tua/wali',
        'admin' => 'Dicatat admin',
    ];

    protected $fillable = [
        'user_id',
        'policy_document_id',
        'policy_type',
        'policy_version',
        'context',
        'actor_user_id',
        'actor_email',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /** Reason: acceptance evidence must not change after the fact. */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Bukti persetujuan kebijakan tidak dapat diubah.');
        });
    }

    /** The account the acceptance belongs to. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /** The accepted document version. */
    public function document(): BelongsTo
    {
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id');
    }
}
