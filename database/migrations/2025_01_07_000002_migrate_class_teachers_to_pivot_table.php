<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing data to pivot table
        DB::statement('
            INSERT INTO class_teacher (class_id, teacher_id, role, created_at, updated_at)
            SELECT id, teacher_id, "teacher", created_at, updated_at
            FROM class_names
            WHERE teacher_id IS NOT NULL
        ');
        
        // Then remove teacher_id column from class_names table
        Schema::table('class_names', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back teacher_id column
        Schema::table('class_names', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
        });
        
        // Migrate data back (get first teacher for each class)
        DB::statement('
            UPDATE class_names 
            SET teacher_id = (
                SELECT teacher_id 
                FROM class_teacher 
                WHERE class_teacher.class_id = class_names.id 
                AND class_teacher.role = "teacher"
                LIMIT 1
            )
        ');
    }
}; 