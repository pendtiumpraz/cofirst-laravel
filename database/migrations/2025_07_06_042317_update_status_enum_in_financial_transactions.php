<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Uses the schema builder's change() (portable across MySQL and SQLite)
     * instead of a raw "ALTER TABLE ... MODIFY" statement, which is MySQL-only.
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])
                ->default('pending')
                ->change();
        });
    }
};
