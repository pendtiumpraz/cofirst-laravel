<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single application setting. Values are JSON encoded so booleans, numbers,
 * strings and arrays round-trip without loss. Read settings through the
 * setting() helper / SettingsManager (cached), never query this model directly
 * from views.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * The admin who last changed this setting.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
