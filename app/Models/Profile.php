<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personal details of a user (one row per user; user_id is unique).
 *
 * Columns: full_name (required), address, phone_number, birth_date, gender
 * (male|female), bio, avatar. `phone` and `date_of_birth` are read/write aliases
 * kept for older code.
 *
 * @property int $user_id
 * @property string $full_name
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property-read int|null $age
 */
class Profile extends Model
{
    use HasFactory;

    /** Age below which a student is treated as a child (needs a parent/guardian). */
    public const ADULT_AGE = 18;

    protected $fillable = [
        'user_id',
        'full_name',
        'address',
        'phone_number',
        'birth_date',
        'gender',
        'bio',
        'avatar',
        'phone',
        'date_of_birth',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /** Get the user that owns this profile. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Age in whole years from birth_date (null when unknown). */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    /** Whether the person is younger than ADULT_AGE (false when unknown). */
    public function isChild(): bool
    {
        return $this->age !== null && $this->age < self::ADULT_AGE;
    }

    /** Alias getter: phone => phone_number. */
    public function getPhoneAttribute(): ?string
    {
        return $this->attributes['phone_number'] ?? null;
    }

    /** Alias setter: phone => phone_number. */
    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone_number'] = $value;
    }

    /** Alias getter: date_of_birth => birth_date. */
    public function getDateOfBirthAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->birth_date;
    }

    /** Alias setter: date_of_birth => birth_date. */
    public function setDateOfBirthAttribute(mixed $value): void
    {
        $this->birth_date = $value;
    }

    /** Indonesian label of the gender. */
    public function getGenderLabelAttribute(): ?string
    {
        return match ($this->gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => null,
        };
    }
}
