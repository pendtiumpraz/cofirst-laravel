<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align enums and columns with what the application actually writes.
 *
 * - enrollments.status: the app writes 'pending' (self-registration) and
 *   'suspended' (admin toggle); both failed the old CHECK/enum. 'cancelled' added
 *   for registrations withdrawn before the class starts. Adds enrollments.notes
 *   (listed in Enrollment::$fillable but missing).
 * - reports.type: the teacher form offers 'incident'. reports.score stores the
 *   optional assessment score (0-100) the teacher form sends.
 * - class_teacher.role: normalised to NOT NULL default 'teacher' (production
 *   drifted to NULLABLE after the old raw MODIFY statement).
 * - project_galleries.photo_path / class_photos.photo_path: nullable, so text-only
 *   projects and photo records awaiting upload are allowed.
 * - messages.content: nullable for image/file messages.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive', 'completed', 'dropped', 'cancelled'])
                ->default('active')->change();
        });

        if (! Schema::hasColumn('enrollments', 'notes')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->text('notes')->nullable();
            });
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->enum('type', ['weekly', 'monthly', 'final', 'incident'])->default('weekly')->change();
        });

        if (! Schema::hasColumn('reports', 'score')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->decimal('score', 5, 2)->nullable();
            });
        }

        DB::table('class_teacher')->whereNull('role')->update(['role' => 'teacher']);
        Schema::table('class_teacher', function (Blueprint $table) {
            $table->enum('role', ['teacher'])->default('teacher')->change();
        });

        Schema::table('project_galleries', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->change();
        });

        Schema::table('class_photos', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->change();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reason: the original enum does not know the new values.
        DB::table('enrollments')->whereIn('status', ['pending', 'suspended', 'cancelled'])->update(['status' => 'inactive']);
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'completed', 'dropped'])->default('active')->change();
        });

        if (Schema::hasColumn('enrollments', 'notes')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        DB::table('reports')->where('type', 'incident')->update(['type' => 'weekly']);
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('type', ['weekly', 'monthly', 'final'])->default('weekly')->change();
        });

        if (Schema::hasColumn('reports', 'score')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropColumn('score');
            });
        }

        DB::table('project_galleries')->whereNull('photo_path')->update(['photo_path' => '']);
        Schema::table('project_galleries', function (Blueprint $table) {
            $table->string('photo_path')->nullable(false)->change();
        });

        DB::table('class_photos')->whereNull('photo_path')->update(['photo_path' => '']);
        Schema::table('class_photos', function (Blueprint $table) {
            $table->string('photo_path')->nullable(false)->change();
        });

        DB::table('messages')->whereNull('content')->update(['content' => '']);
        Schema::table('messages', function (Blueprint $table) {
            $table->text('content')->nullable(false)->change();
        });
    }
};
