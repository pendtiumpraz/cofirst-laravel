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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('completion'); // completion, achievement, participation
            $table->string('background_image')->nullable();
            $table->json('layout_config'); // positions for elements
            $table->text('content_template'); // HTML/Blade template with variables
            $table->json('available_variables'); // list of available variables
            $table->string('orientation')->default('landscape'); // landscape, portrait
            $table->string('size')->default('A4'); // A4, Letter, etc
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};