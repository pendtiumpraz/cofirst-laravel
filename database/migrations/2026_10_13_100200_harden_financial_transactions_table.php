<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make financial_transactions usable from the UI and safe to keep for years.
 *
 * - payment_method becomes NULLABLE (unknown until the invoice is paid) and gains
 *   qris / virtual_account. Before this, every manual "record payment" failed with
 *   "NOT NULL constraint failed" because the form never sends a method.
 * - transaction_date becomes NOT NULL DATETIME with a CURRENT_TIMESTAMP default
 *   (it was a nullable TIMESTAMP that the app treats as required).
 * - status is normalised to NOT NULL default 'pending' (production drifted to
 *   NULLABLE after the old raw MODIFY statement).
 * - amount is widened to decimal(15,2) (decimal(10,2) capped at Rp 99.999.999).
 * - New nullable columns for invoices/refunds/verification and soft deletes.
 * - student_id is now RESTRICT and course_id SET NULL, so deleting a user or a
 *   course can no longer silently erase payment history.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reason: backfill before tightening NOT NULL constraints so existing rows survive.
        DB::table('financial_transactions')->whereNull('transaction_date')
            ->update(['transaction_date' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)')]);
        DB::table('financial_transactions')->whereNull('status')->update(['status' => 'pending']);

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['course_id']);
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
            $table->dateTime('transaction_date')->useCurrent()->change();
            $table->enum('payment_method', ['cash', 'transfer', 'card', 'ewallet', 'qris', 'virtual_account'])
                ->nullable()->change();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])
                ->default('pending')->change();
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('invoice_number', 50)->nullable()->unique();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            // The parent/guardian who pays for a child (NULL for adults paying themselves).
            $table->foreignId('payer_id')->nullable()->constrained('users')->nullOnDelete();
            // A refund row points at the payment it refunds.
            $table->foreignId('refund_of_id')->nullable()->constrained('financial_transactions')->nullOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->string('proof_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();

            $table->index(['student_id', 'status'], 'fin_tx_student_status_index');
            $table->index(['status', 'due_date'], 'fin_tx_status_due_index');
            $table->index(['type', 'status'], 'fin_tx_type_status_index');
            $table->index('transaction_date', 'fin_tx_transaction_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            // Reason: foreign keys go first. On MySQL/MariaDB fin_tx_student_status_index
            // replaced the implicit index of the student_id foreign key, so it can only
            // be dropped once that key is gone (error 1553 otherwise).
            $table->dropForeign(['student_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['enrollment_id']);
            $table->dropForeign(['payer_id']);
            $table->dropForeign(['refund_of_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropForeign(['verified_by']);
            $table->dropIndex('fin_tx_student_status_index');
            $table->dropIndex('fin_tx_status_due_index');
            $table->dropIndex('fin_tx_type_status_index');
            $table->dropIndex('fin_tx_transaction_date_index');
            $table->dropUnique(['invoice_number']);
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number', 'enrollment_id', 'payer_id', 'refund_of_id', 'discount_amount',
                'tax_amount', 'proof_path', 'recorded_by', 'verified_by', 'verified_at', 'deleted_at',
            ]);
        });

        // Reason: the original enum has no qris/virtual_account and no NULLs.
        DB::table('financial_transactions')->whereNull('payment_method')->update(['payment_method' => 'cash']);
        DB::table('financial_transactions')->whereIn('payment_method', ['qris', 'virtual_account'])
            ->update(['payment_method' => 'transfer']);

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
            $table->timestamp('transaction_date')->nullable()->change();
            $table->enum('payment_method', ['cash', 'transfer', 'card', 'ewallet'])->change();
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });
    }
};
