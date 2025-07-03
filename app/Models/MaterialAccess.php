<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialAccess extends Model
{
    use HasFactory;

    protected $table = 'material_access';

    protected $fillable = [
        'student_id',
        'material_id',
        'accessed_at',
        'access_duration_seconds',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'access_duration_seconds' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // Get total access time for a student on a material
    public static function getTotalAccessTime($studentId, $materialId)
    {
        return self::where('student_id', $studentId)
                  ->where('material_id', $materialId)
                  ->sum('access_duration_seconds');
    }

    // Get access count for a student on a material
    public static function getAccessCount($studentId, $materialId)
    {
        return self::where('student_id', $studentId)
                  ->where('material_id', $materialId)
                  ->count();
    }

    // Get last access time for a student on a material
    public static function getLastAccessTime($studentId, $materialId)
    {
        $access = self::where('student_id', $studentId)
                     ->where('material_id', $materialId)
                     ->latest('accessed_at')
                     ->first();
        
        return $access ? $access->accessed_at : null;
    }

    // Record material access
    public static function recordAccess($studentId, $materialId, $durationSeconds = 0)
    {
        return self::create([
            'student_id' => $studentId,
            'material_id' => $materialId,
            'accessed_at' => now(),
            'access_duration_seconds' => $durationSeconds,
        ]);
    }

    // Get formatted duration
    public function getFormattedDurationAttribute()
    {
        $seconds = $this->access_duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}