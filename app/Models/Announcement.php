<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An announcement shown on dashboards between start_date and end_date.
 * user_id (the author) is nullable: announcements survive their author.
 */
class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /** The author. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Alias of user(). */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Announcements visible today (start_date <= today <= end_date). */
    public function scopeCurrent(Builder $query): Builder
    {
        $today = today()->toDateString();

        return $query->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today);
    }

    /** Announcements that start in the future. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('start_date', '>', today()->toDateString());
    }
}
