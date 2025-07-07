<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('class_names', function (Blueprint $table) {
            // Add delivery_method column if it doesn't exist
            if (!Schema::hasColumn('class_names', 'delivery_method')) {
                $table->enum('delivery_method', ['online', 'offline'])->after('status')->nullable();
            }

            // Add or modify type column
            if (Schema::hasColumn('class_names', 'type')) {
                // If type column exists, drop it first
                $table->dropColumn('type');
            }
            
            $table->enum('type', [
                'private_home_call',
                'private_office_1on1',
                'private_online_1on1',
                'public_school_extracurricular',
                'offline_seminar',
                'online_webinar',
                'group_class_3_5_kids',
                'free_webinar',
                'free_trial_30min'
            ])->after('delivery_method')->nullable();

            // Add curriculum_id column if it doesn't exist
            if (!Schema::hasColumn('class_names', 'curriculum_id')) {
                $table->foreignId('curriculum_id')->nullable()->constrained('curriculums')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_names', function (Blueprint $table) {
            // Reverse curriculum_id if it exists
            if (Schema::hasColumn('class_names', 'curriculum_id')) {
                $table->dropForeign(['curriculum_id']);
                $table->dropColumn('curriculum_id');
            }

            // Revert type column to its previous state if it exists
            if (Schema::hasColumn('class_names', 'type')) {
                $table->dropColumn('type');
                $table->enum('type', ['private', 'group', 'extracurricular'])->nullable(); // Revert to original enum values
            }

            // Remove delivery_method column if it exists
            if (Schema::hasColumn('class_names', 'delivery_method')) {
                $table->dropColumn('delivery_method');
            }
        });
    }
};