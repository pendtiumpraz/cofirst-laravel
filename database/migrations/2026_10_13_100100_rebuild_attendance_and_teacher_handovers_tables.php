<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Give the stub tables `attendance` and `teacher_handovers` a real schema.
 *
 * Both were created in 2024_12_02 with only id + timestamps and no model ever
 * wrote to them, so they are dropped and recreated (only while they are still
 * stubs, so a re-run never destroys real data).
 *
 * attendance: one row per student per class meeting date (status present /
 * late / absent / excused), optionally linked to the weekly schedule slot.
 *
 * teacher_handovers: a request to move a class (or one student of it) from one
 * teacher to another, permanently or for a period, with an approval workflow.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendance') && ! Schema::hasColumn('attendance', 'student_id')) {
            Schema::drop('attendance');
        }

        if (! Schema::hasTable('attendance')) {
            Schema::create('attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('class_names')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
                // Teacher who taught / recorded the meeting (substitutes included).
                $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedSmallInteger('meeting_number')->nullable();
                $table->date('attendance_date');
                $table->enum('status', ['present', 'late', 'absent', 'excused'])->default('present');
                $table->time('check_in_time')->nullable();
                $table->unsignedSmallInteger('minutes_late')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['class_id', 'student_id', 'attendance_date']);
                $table->index(['student_id', 'status']);
                $table->index(['class_id', 'attendance_date']);
            });
        }

        if (Schema::hasTable('teacher_handovers') && ! Schema::hasColumn('teacher_handovers', 'class_id')) {
            Schema::drop('teacher_handovers');
        }

        if (! Schema::hasTable('teacher_handovers')) {
            Schema::create('teacher_handovers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('class_names')->cascadeOnDelete();
                $table->foreignId('from_teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_teacher_id')->nullable()->constrained('users')->nullOnDelete();
                // NULL = the whole class is handed over.
                $table->foreignId('student_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->enum('type', ['permanent', 'temporary'])->default('permanent');
                $table->date('effective_from');
                $table->date('effective_until')->nullable();
                $table->text('reason');
                $table->text('handover_notes')->nullable();
                $table->unsignedSmallInteger('last_meeting_number')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'effective_from']);
                $table->index(['from_teacher_id', 'status']);
                $table->index(['to_teacher_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations (back to the original id + timestamps stubs).
     */
    public function down(): void
    {
        foreach (['attendance', 'teacher_handovers'] as $name) {
            Schema::dropIfExists($name);

            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
    }
};
