<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (!$course->slug) {
                $course->slug = Str::slug($course->name);
            }
        });
    }

    /**
     * Get the classes for this course.
     */
    public function classes()
    {
        return $this->hasMany(ClassName::class);
    }

    /**
     * Get the curriculum for this course.
     */
    public function curriculum()
    {
        return $this->hasOne(Curriculum::class);
    }

    /**
     * Get the financial transactions for this course.
     */
    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Scope to get only active courses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
