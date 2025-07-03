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
        // Add curriculum type and status to curriculums table
        Schema::table('curriculums', function (Blueprint $table) {
            $table->enum('type', ['fast-track', 'regular', 'expert', 'beginner'])->default('regular')->after('description');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('type');
            $table->integer('duration_weeks')->nullable()->after('status');
            $table->text('objectives')->nullable()->after('duration_weeks');
        });

        // Add meeting details and order to syllabuses table
        Schema::table('syllabuses', function (Blueprint $table) {
            $table->integer('meeting_number')->after('curriculum_id');
            $table->text('learning_objectives')->nullable()->after('description');
            $table->text('activities')->nullable()->after('learning_objectives');
            $table->integer('duration_minutes')->default(90)->after('activities');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('duration_minutes');
        });

        // Add material details and meeting span to materials table
        Schema::table('materials', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->integer('meeting_start')->after('content'); // Starting meeting number
            $table->integer('meeting_end')->after('meeting_start'); // Ending meeting number (can be same as start)
            $table->enum('type', ['document', 'video', 'exercise', 'quiz', 'project'])->default('document')->after('meeting_end');
            $table->string('file_path')->nullable()->after('type'); // For file uploads
            $table->text('external_url')->nullable()->after('file_path'); // For external resources
            $table->enum('status', ['active', 'inactive'])->default('active')->after('external_url');
            $table->integer('order')->default(1)->after('status'); // Order within syllabus
        });

        // Create class_progress table to track student progress
        Schema::create('class_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('class_names')->onDelete('cascade');
            $table->foreignId('syllabus_id')->constrained('syllabuses')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials')->onDelete('set null');
            $table->integer('meeting_number');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure unique progress per student per class per meeting
            $table->unique(['student_id', 'class_id', 'meeting_number']);
        });

        // Create material_access table to track material access
        Schema::create('material_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->timestamp('accessed_at');
            $table->integer('access_duration_seconds')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_access');
        Schema::dropIfExists('class_progress');
        
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'meeting_start', 'meeting_end', 'type', 
                'file_path', 'external_url', 'status', 'order'
            ]);
        });
        
        Schema::table('syllabuses', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_number', 'learning_objectives', 'activities', 
                'duration_minutes', 'status'
            ]);
        });
        
        Schema::table('curriculums', function (Blueprint $table) {
            $table->dropColumn(['type', 'status', 'duration_weeks', 'objectives']);
        });
    }
};