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
        // First, update the enum to include 'teacher' option
        DB::statement("ALTER TABLE class_teacher MODIFY COLUMN role ENUM('primary', 'assistant', 'substitute', 'teacher') DEFAULT 'teacher'");
        
        // Then update all existing roles to 'teacher' since all teachers are now equal
        DB::table('class_teacher')->update(['role' => 'teacher']);
        
        // Finally, update the enum to only allow 'teacher' role
        DB::statement("ALTER TABLE class_teacher MODIFY COLUMN role ENUM('teacher') DEFAULT 'teacher'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum with primary, assistant, substitute
        DB::statement("ALTER TABLE class_teacher MODIFY COLUMN role ENUM('primary', 'assistant', 'substitute') DEFAULT 'primary'");
        
        // Update any 'teacher' roles to 'primary' for backward compatibility
        DB::table('class_teacher')->where('role', 'teacher')->update(['role' => 'primary']);
    }
};
