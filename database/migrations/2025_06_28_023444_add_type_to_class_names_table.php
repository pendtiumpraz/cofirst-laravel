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
            $table->enum('type', ['private', 'group', 'extracurricular'])->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_names', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};