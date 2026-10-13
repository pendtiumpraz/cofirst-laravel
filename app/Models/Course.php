<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A course in the catalogue (e.g. Scratch, Python). A course has one or more
 * curriculums and is taught in classes (ClassName).
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $price Decimal string; use formatted_price for display.
 */
class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'benefits',
        'level',
        'duration_weeks',
        'is_active',
        'thumbnail',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'duration_weeks' => 'integer',
    ];

    /**
     * Boot the model: generate a unique slug when none is given.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Course $course) {
            if (! $course->slug) {
                $course->slug = static::uniqueSlug((string) $course->name);
            }
        });
    }

    /**
     * Build a slug from the name that no other course (including archived ones) uses.
     *
     * Args:
     *     name (string): Course name.
     *     ignoreId (int|null): Course id to ignore (when renaming).
     *
     * Returns:
     *     string: e.g. "python", "python-2".
     */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kursus';
        $slug = $base;
        $suffix = 2;

        while (static::withTrashed()->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /** The classes of this course. */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassName::class);
    }

    /** Classes that are planned or running. */
    public function activeClasses(): HasMany
    {
        return $this->classes()->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])
            ->where('is_active', true);
    }

    /** All curriculums of this course. */
    public function curriculums(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    /**
     * The main (oldest) curriculum of this course.
     *
     * Reason: a course can have several curriculums; oldestOfMany() makes lazy and
     * eager loading return the same row.
     */
    public function curriculum(): HasOne
    {
        return $this->hasOne(Curriculum::class)->oldestOfMany();
    }

    /** Enrollments registered directly for this course. */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** The financial transactions for this course. */
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /** Certificates issued for this course. */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** Scope to get only active courses. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('courses.is_active', true);
    }

    /** Price as an integer number of rupiah. */
    public function getPriceRupiahAttribute(): int
    {
        return (int) round((float) $this->price);
    }

    /** Get the formatted price, e.g. "Rp 1.500.000". */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }
}
