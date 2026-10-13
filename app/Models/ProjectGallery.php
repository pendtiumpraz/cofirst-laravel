<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's project shown in the project gallery.
 *
 * photo_path is nullable (text-only project); `photo_url` returns null then.
 */
class ProjectGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'title',
        'description',
        'photo_path',
        'thumbnail_path',
        'is_featured',
        'views',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    /** The student who made the project. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** The class (legacy name). */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** The class. */
    public function className(): BelongsTo
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /** Featured projects. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('project_galleries.is_featured', true);
    }

    /** Increment the view counter. */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /** Public URL of the photo, or null when there is none. */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    /** Public URL of the thumbnail, falling back to the photo. */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/'.$this->thumbnail_path) : $this->photo_url;
    }
}
