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
        Schema::table('schedules', function (Blueprint $table) {
            // Remove existing columns
            $table->dropColumn(['title', 'description', 'meeting_link', 'status', 'start_time', 'end_time']);

            // Add new columns
            $table->date('schedule_date')->after('class_id');
            $table->time('schedule_time')->after('schedule_date');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('teacher_assignment_id')->nullable()->constrained('teacher_assignments')->onDelete('cascade');
            $table->boolean('is_active')->default(true)->after('teacher_assignment_id'); // Add is_active column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Revert changes: remove new columns
            $table->dropForeign(['enrollment_id']);
            $table->dropColumn(['schedule_date', 'schedule_time', 'enrollment_id', 'teacher_assignment_id', 'is_active']);

            // Re-add original columns (if necessary, based on your application's needs)
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->string('meeting_link')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
        });
    }
};