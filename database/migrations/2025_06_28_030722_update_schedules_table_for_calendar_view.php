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
            $table->dropColumn(['title', 'description', 'meeting_link', 'status']);

            // Modify existing time columns to be time only (not datetime)
            $table->dropColumn(['start_time', 'end_time']);
            $table->time('start_time')->after('class_id');
            $table->time('end_time')->after('start_time');

            // Add new columns for weekly schedule
            $table->integer('day_of_week')->after('class_id')->comment('1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu');
            $table->string('room')->nullable()->after('end_time');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('teacher_assignment_id')->nullable()->constrained('teacher_assignments')->onDelete('cascade');
            $table->boolean('is_active')->default(true)->after('teacher_assignment_id');
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
            $table->dropForeign(['teacher_assignment_id']);
            $table->dropColumn(['day_of_week', 'room', 'enrollment_id', 'teacher_assignment_id', 'is_active']);

            // Revert time columns to datetime
            $table->dropColumn(['start_time', 'end_time']);
            $table->datetime('start_time');
            $table->datetime('end_time');

            // Re-add original columns
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('meeting_link')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
        });
    }
};