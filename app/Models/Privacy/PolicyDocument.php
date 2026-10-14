<?php

namespace App\Models\Privacy;

use App\Models\User;
use App\Services\Privacy\PolicyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/**
 * A versioned legal document: privacy notice, terms, cookie policy, children's notice or
 * the DPA template.
 *
 * Lifecycle: draft (editable) -> published (immutable; effective from effective_at). The
 * current version of a type is the latest published one whose effective_at has passed; a
 * published version with a future effective_at is "scheduled" so people are told about a
 * change before it applies (UU 27/2022 Pasal 21 ayat (2); PP 33/2026 Pasal 62 ayat (5)).
 *
 * @property int $id
 * @property string $type
 * @property int $revision
 * @property string $version
 * @property string $title
 * @property string|null $summary
 * @property string $body Markdown, may contain {{variables}} (see PolicyService::variables())
 * @property string|null $changelog
 * @property string $status draft|published
 * @property bool $requires_reacceptance
 * @property \Illuminate\Support\Carbon|null $effective_at
 * @property \Illuminate\Support\Carbon|null $published_at
 */
class PolicyDocument extends Model
{
    public const TYPES = [
        'privacy_notice' => 'Kebijakan Privasi',
        'terms' => 'Syarat & Ketentuan',
        'cookie_policy' => 'Kebijakan Cookie',
        'children_notice' => 'Privasi untuk Anak & Orang Tua',
        'dpa_template' => 'Templat Perjanjian Pemrosesan Data',
    ];

    /** Public route of each type (null = not public). */
    public const ROUTES = [
        'privacy_notice' => 'privacy.notice',
        'terms' => 'legal.terms',
        'cookie_policy' => 'privacy.cookies',
        'children_notice' => 'privacy.children',
        'dpa_template' => null,
    ];

    protected $fillable = [
        'type',
        'revision',
        'version',
        'title',
        'summary',
        'body',
        'changelog',
        'status',
        'requires_reacceptance',
        'effective_at',
        'published_at',
        'published_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'revision' => 'integer',
        'requires_reacceptance' => 'boolean',
        'effective_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Published versions are immutable evidence; only drafts may change or be deleted.
     */
    protected static function booted(): void
    {
        static::updating(function (PolicyDocument $doc) {
            // Reason: publishing itself is the one allowed change of a draft's status.
            if ($doc->getOriginal('status') === 'published') {
                throw new LogicException('Versi yang sudah terbit tidak dapat diubah. Buat versi baru.');
            }
        });

        static::deleting(function (PolicyDocument $doc) {
            if ($doc->getOriginal('status') === 'published') {
                throw new LogicException('Versi yang sudah terbit tidak dapat dihapus.');
            }
        });

        // Reason: PolicyService caches the list of published versions.
        static::saved(fn () => PolicyService::flushCache());
        static::deleted(fn () => PolicyService::flushCache());
    }

    /** Acceptances of this version. */
    public function acceptances(): HasMany
    {
        return $this->hasMany(PolicyAcceptance::class);
    }

    /** Who published it. */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by')->withTrashed();
    }

    /** Who wrote the draft. */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /** Only published versions. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** Published versions already in force. */
    public function scopeEffective(Builder $query): Builder
    {
        return $query->published()->where(function (Builder $q) {
            $q->whereNull('effective_at')->orWhere('effective_at', '<=', now());
        });
    }

    /** Versions of one type. */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** Whether the document is still an editable draft. */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /** Whether the document is published. */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /** Whether it is published but not yet in force. */
    public function isScheduled(): bool
    {
        return $this->isPublished() && $this->effective_at !== null && $this->effective_at->isFuture();
    }

    /** Indonesian label of the document type. */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /** Public URL of the type's page, if any. */
    public function publicUrl(): ?string
    {
        $route = self::ROUTES[$this->type] ?? null;

        return $route && \Illuminate\Support\Facades\Route::has($route) ? route($route) : null;
    }

    /**
     * Next free revision number for a type.
     *
     * Args:
     *     type (string): Document type key.
     *
     * Returns:
     *     int: max(revision) + 1.
     */
    public static function nextRevision(string $type): int
    {
        return (int) static::query()->where('type', $type)->max('revision') + 1;
    }
}
