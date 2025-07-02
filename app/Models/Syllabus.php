<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'syllabuses';

    protected $fillable = [
        'curriculum_id',
        'title',
        'description',
    ];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}