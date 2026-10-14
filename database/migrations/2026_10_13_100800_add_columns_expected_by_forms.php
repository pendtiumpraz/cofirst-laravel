<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the existing admin forms already write but the schema never had.
 *
 * - courses.duration_hours: the course create/edit form asks for "Duration
 *   (Hours)"; the value was silently dropped.
 * - class_photos.title / description / date_taken: the admin class-photo edit form
 *   writes them (the UPDATE failed) and the class-photo views display them.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('courses', 'duration_hours')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedSmallInteger('duration_hours')->nullable();
            });
        }

        if (! Schema::hasColumn('class_photos', 'title')) {
            Schema::table('class_photos', function (Blueprint $table) {
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->date('date_taken')->nullable();
                $table->index(['class_id', 'date_taken'], 'class_photos_class_date_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('class_photos', 'title')) {
            // Reason: MySQL/MariaDB silently dropped the implicit index of the class_id
            // foreign key when class_photos_class_date_index was created, and refuse to
            // drop the composite index without it (error 1553). Restore it first.
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)
                && ! Schema::hasIndex('class_photos', 'class_photos_class_id_foreign')) {
                Schema::table('class_photos', function (Blueprint $table) {
                    $table->index('class_id', 'class_photos_class_id_foreign');
                });
            }

            Schema::table('class_photos', function (Blueprint $table) {
                $table->dropIndex('class_photos_class_date_index');
            });
            Schema::table('class_photos', function (Blueprint $table) {
                $table->dropColumn(['title', 'description', 'date_taken']);
            });
        }

        if (Schema::hasColumn('courses', 'duration_hours')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('duration_hours');
            });
        }
    }
};
