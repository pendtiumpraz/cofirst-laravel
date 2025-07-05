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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('type'); // physical, digital, privilege, discount
            $table->integer('points_cost');
            $table->integer('quantity_available')->nullable(); // null = unlimited
            $table->integer('quantity_redeemed')->default(0);
            $table->string('image')->nullable();
            $table->json('metadata')->nullable(); // Extra data like discount percentage
            $table->boolean('is_active')->default(true);
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'points_cost']);
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};