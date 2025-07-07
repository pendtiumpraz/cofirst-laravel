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
        Schema::table('enrollments', function (Blueprint $table) {
            // Add course_id and make class_id nullable
            $table->foreignId('course_id')->nullable()->after('id')->constrained('courses')->onDelete('cascade');
            
            // Make class_id nullable for cases where class is not yet assigned
            $table->foreignId('class_id')->nullable()->change();
            
            // Add payment_status column
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'refunded'])->default('unpaid')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
            $table->dropColumn('payment_status');
            
            // Make class_id required again
            $table->foreignId('class_id')->nullable(false)->change();
        });
    }
};