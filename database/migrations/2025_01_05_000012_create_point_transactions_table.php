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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points'); // Can be positive (earned) or negative (spent)
            $table->string('type'); // earned, spent, bonus, penalty
            $table->string('reason'); // login, assignment_complete, quiz_perfect, etc.
            $table->text('description')->nullable();
            $table->morphs('related'); // Related model (Assignment, Quiz, etc.)
            $table->json('metadata')->nullable(); // Extra data like quiz score, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};