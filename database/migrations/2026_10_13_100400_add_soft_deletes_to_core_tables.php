<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add deleted_at (soft deletes) to the core academic tables and users.
 *
 * Deleting a user, course, class, curriculum, syllabus or material used to cascade
 * into enrollments, payments, reports and certificates. With soft deletes the
 * admin "delete" archives the row and the history stays intact.
 * (financial_transactions gets its deleted_at in 2026_10_13_100200.)
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = ['users', 'courses', 'class_names', 'curriculums', 'syllabuses', 'materials'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
