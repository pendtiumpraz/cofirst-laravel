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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('template_id')->constrained('certificate_templates');
            $table->foreignId('student_id')->constrained('users');
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->foreignId('class_id')->nullable()->constrained('class_names');
            $table->string('type'); // completion, achievement, participation
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->json('metadata')->nullable(); // additional data like score, grade, etc
            $table->string('qr_code')->nullable();
            $table->string('verification_code')->unique();
            $table->string('file_path')->nullable(); // path to generated PDF
            $table->boolean('is_valid')->default(true);
            $table->foreignId('issued_by')->constrained('users'); // admin/teacher who issued
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'course_id']);
            $table->index('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};