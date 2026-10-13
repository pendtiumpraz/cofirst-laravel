<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * All teachers of a class are equal, so the pivot role collapses to "teacher".
     * Uses portable schema-builder change() calls instead of MySQL-only MODIFY.
     */
    public function up(): void
    {
        Schema::table('class_teacher', function (Blueprint $table) {
            $table->enum('role', ['primary', 'assistant', 'substitute', 'teacher'])
                ->default('teacher')
                ->change();
        });

        DB::table('class_teacher')->update(['role' => 'teacher']);

        Schema::table('class_teacher', function (Blueprint $table) {
            $table->enum('role', ['teacher'])->default('teacher')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_teacher', function (Blueprint $table) {
            $table->enum('role', ['primary', 'assistant', 'substitute', 'teacher'])
                ->default('primary')
                ->change();
        });

        DB::table('class_teacher')->where('role', 'teacher')->update(['role' => 'primary']);

        Schema::table('class_teacher', function (Blueprint $table) {
            $table->enum('role', ['primary', 'assistant', 'substitute'])->default('primary')->change();
        });
    }
};
