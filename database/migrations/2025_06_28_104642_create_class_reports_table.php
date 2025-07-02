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
        Schema::create('class_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('class_names')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('report_date');
            $table->time('start_time'); // Waktu mulai kelas sesuai jadwal
            $table->time('end_time');   // Waktu selesai kelas (input manual)
            $table->integer('meeting_number'); // Pertemuan ke-
            $table->foreignId('curriculum_id')->constrained('curriculums')->onDelete('cascade');
            $table->foreignId('syllabus_id')->constrained('syllabuses')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->text('learning_concept'); // Materi/konsep pembelajaran

            // Taksonomi Bloom
            $table->text('remember_understanding')->nullable();
            $table->text('understand_comprehension')->nullable();
            $table->text('apply_application')->nullable();
            $table->text('analyze_analysis')->nullable();
            $table->text('evaluate_evaluation')->nullable();
            $table->text('create_creation')->nullable();

            $table->text('notes_recommendations')->nullable(); // Catatan & rekomendasi
            $table->text('follow_up_notes')->nullable(); // Catatan tindak lanjut
            $table->string('learning_media_link')->nullable(); // Link Google Drive untuk foto/video

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_reports');
    }
};