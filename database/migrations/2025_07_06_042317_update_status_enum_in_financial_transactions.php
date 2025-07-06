<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'cancelled'
        DB::statement("ALTER TABLE financial_transactions MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE financial_transactions MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
    }
};
