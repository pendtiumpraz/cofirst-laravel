<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalise list-like TEXT columns to plain newline-separated text.
 *
 * curriculums.objectives, syllabuses.learning_objectives and syllabuses.activities
 * held a mix of JSON arrays (written by the seeder through a 'json' cast) and plain
 * strings (written by the admin forms), which crashed views that echo them.
 * They are now stored as one item per line; the models expose them as a string
 * (for textareas) and as `*_list` arrays (for bullet lists). Data-only migration.
 */
return new class extends Migration
{
    /** @var array<string, array<int, string>> */
    private array $columns = [
        'curriculums' => ['objectives'],
        'syllabuses' => ['learning_objectives', 'activities'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->columns as $table => $columns) {
            DB::table($table)->select(array_merge(['id'], $columns))->orderBy('id')
                ->chunk(200, function ($rows) use ($table, $columns) {
                    foreach ($rows as $row) {
                        $changes = [];

                        foreach ($columns as $column) {
                            $normalized = $this->normalize($row->{$column});

                            if ($normalized !== $row->{$column}) {
                                $changes[$column] = $normalized;
                            }
                        }

                        if ($changes) {
                            DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations (data-only; nothing to undo).
     */
    public function down(): void
    {
        //
    }

    /**
     * Turn a JSON list / JSON string into newline-separated text.
     */
    private function normalize(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return implode("\n", array_map('strval', array_values(array_filter($decoded, 'is_scalar'))));
        }

        return is_string($decoded) ? $decoded : $value;
    }
};
