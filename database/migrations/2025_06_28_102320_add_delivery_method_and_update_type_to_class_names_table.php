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
            // Add delivery_method column
            $table->enum('delivery_method', ['online', 'offline'])->after('status')->nullable();

            // Change type column to allow more specific options
            // First, drop the existing column if it exists and then re-add with new enum values
            // This is a common way to modify enum columns in SQLite
            $table->dropColumn('type');
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

            // Add curriculum_id column
            $table->foreignId('curriculum_id')->nullable()->constrained('curriculums')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_names', function (Blueprint $table) {
            // Reverse curriculum_id
            $table->dropForeign(['curriculum_id']);
            $table->dropColumn('curriculum_id');

            // Revert type column to its previous state (or a default if original is unknown)
            $table->dropColumn('type');
            $table->enum('type', ['private', 'group', 'extracurricular'])->nullable(); // Revert to original enum values

            // Remove delivery_method column
            $table->dropColumn('delivery_method');
        });
    }
};