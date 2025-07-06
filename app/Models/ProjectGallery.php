<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    public function incrementViews()
    {
        $this->increment('views');
    }
}