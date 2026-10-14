<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A photo taken in a class, uploaded by a teacher/admin.
 *
 * photo_path is nullable (record created before the upload). title,
 * description and date_taken (date) are used by the admin form and views. `teacher` is an
 * alias of `uploader` (a User) for older views; use `$photo->uploader?->name`.
 */
class ClassPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'date_taken',
        'photo_path',
        'caption',
        'uploaded_by',
    ];

    protected $casts = [
        'date_taken' => 'date',
    ];

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

    /** The user who uploaded the photo. */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Alias of uploader() (a User, not a teacher profile). */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Public URL of the photo, or null when no file was uploaded. */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }
}
