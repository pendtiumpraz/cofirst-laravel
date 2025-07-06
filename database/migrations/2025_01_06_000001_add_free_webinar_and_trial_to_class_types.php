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
        // In MySQL, we need to alter the ENUM column
        // This requires using raw SQL because Laravel doesn't support modifying ENUMs directly
        DB::statement("ALTER TABLE class_names MODIFY COLUMN type ENUM(
            'private_home_call',
            'private_office_1on1',
            'private_online_1on1',
            'public_school_extracurricular',
            'offline_seminar',
            'online_webinar',
            'group_class_3_5_kids',
            'free_webinar',
            'free_trial_30min'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE class_names MODIFY COLUMN type ENUM(
            'private_home_call',
            'private_office_1on1',
            'private_online_1on1',
            'public_school_extracurricular',
            'offline_seminar',
            'online_webinar',
            'group_class_3_5_kids'
        ) NULL");
    }
};