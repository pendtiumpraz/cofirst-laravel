<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'title',
        'content',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
