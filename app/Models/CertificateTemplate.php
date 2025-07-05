<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'background_image',
        'layout_config',
        'content_template',
        'available_variables',
        'orientation',
        'size',
        'is_active',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'available_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the certificates using this template.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }

    /**
     * Get default variables for certificate generation
     */
    public function getDefaultVariables()
    {
        return [
            'student_name' => '',
            'course_name' => '',
            'class_name' => '',
            'issue_date' => '',
            'certificate_number' => '',
            'teacher_name' => '',
            'grade' => '',
            'score' => '',
            'duration' => '',
            'company_name' => 'CoFirst',
            'company_tagline' => 'Learn Coding with Japanese Quality',
        ];
    }

    /**
     * Get background image URL
     */
    public function getBackgroundImageUrlAttribute()
    {
        return $this->background_image 
            ? asset('storage/' . $this->background_image)
            : asset('images/certificate-bg-default.jpg');
    }
}