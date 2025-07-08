<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'photo_path',
        'caption',
        'uploaded_by',
    ];

    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    public function className()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}