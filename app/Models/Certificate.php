<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_number',
        'template_id',
        'student_id',
        'course_id',
        'class_id',
        'type',
        'title',
        'description',
        'issue_date',
        'expiry_date',
        'metadata',
        'qr_code',
        'verification_code',
        'file_path',
        'is_valid',
        'issued_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'metadata' => 'array',
        'is_valid' => 'boolean',
    ];

    /**
     * Boot function to generate certificate number and verification code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
            if (empty($certificate->verification_code)) {
                $certificate->verification_code = self::generateVerificationCode();
            }
        });
    }

    /**
     * Generate unique certificate number
     */
    public static function generateCertificateNumber()
    {
        do {
            $number = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('certificate_number', $number)->exists());

        return $number;
    }

    /**
     * Generate unique verification code
     */
    public static function generateVerificationCode()
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    /**
     * Get the template
     */
    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    /**
     * Get the student
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the course
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the class
     */
    public function class()
    {
        return $this->belongsTo(ClassName::class, 'class_id');
    }

    /**
     * Get the issuer
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get certificate file URL
     */
    public function getFileUrlAttribute()
    {
        return $this->file_path 
            ? asset('storage/' . $this->file_path)
            : null;
    }

    /**
     * Get QR code URL
     */
    public function getQrCodeUrlAttribute()
    {
        return $this->qr_code 
            ? asset('storage/' . $this->qr_code)
            : null;
    }

    /**
     * Get verification URL
     */
    public function getVerificationUrlAttribute()
    {
        return route('certificate.verify', $this->verification_code);
    }

    /**
     * Check if certificate is expired
     */
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    /**
     * Invalidate certificate
     */
    public function invalidate()
    {
        $this->update(['is_valid' => false]);
    }
}