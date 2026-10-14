<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UU No. 27/2022 (PDP) consent & transparency module.
 *
 * - consent_purposes: register of processing purposes and their legal basis (Pasal 20).
 * - consent_records: append-only consent ledger; proof of consent (Pasal 24, PP 33/2026 Pasal 36).
 * - policy_documents: versioned privacy notice, terms, cookie policy, children's notice and
 *   DPA template (Pasal 21 ayat (2): changes are announced before they take effect).
 * - policy_acceptances: who accepted which version, when and how.
 * - parental_consent_requests: guardian consent for children (Pasal 25, PP 33/2026 Pasal 38).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consent_purposes', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 150);
            $table->text('description');
            $table->string('category', 32)->index();
            $table->string('legal_basis', 32)->default('consent');
            $table->boolean('is_required')->default(false);
            $table->json('applies_to')->nullable();
            $table->string('audience', 16)->default('all');
            $table->string('data_used', 255)->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('policy_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->unsignedInteger('revision');
            $table->string('version', 20);
            $table->string('title', 150);
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->text('changelog')->nullable();
            $table->string('status', 16)->default('draft');
            $table->boolean('requires_reacceptance')->default(false);
            $table->timestamp('effective_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'revision']);
            $table->unique(['type', 'version']);
            $table->index(['type', 'status', 'effective_at']);
        });

        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_email', 191)->nullable();
            $table->string('actor_name', 150)->nullable();
            $table->string('actor_role', 16)->nullable();
            $table->foreignId('consent_purpose_id')->nullable()->constrained('consent_purposes')->nullOnDelete();
            $table->string('purpose_key', 64);
            $table->unsignedInteger('purpose_version')->default(1);
            $table->string('decision', 16);
            $table->string('method', 24);
            $table->foreignId('policy_document_id')->nullable()->constrained('policy_documents')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('evidence')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_user_id', 'purpose_key', 'id'], 'consent_records_subject_purpose_index');
            $table->index(['purpose_key', 'decision']);
            $table->index('created_at');
        });

        Schema::create('policy_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('policy_document_id')->constrained('policy_documents')->restrictOnDelete();
            $table->string('policy_type', 32);
            $table->string('policy_version', 20);
            $table->string('context', 24);
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_email', 191)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('accepted_at')->useCurrent();

            $table->index(['user_id', 'policy_type']);
        });

        Schema::create('parental_consent_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guardian_name', 150)->nullable();
            $table->string('guardian_email', 191)->index();
            $table->string('guardian_relationship', 16)->nullable();
            $table->foreignId('guardian_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('status', 16)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('send_count')->default(0);
            $table->timestamp('responded_at')->nullable();
            $table->string('response_ip', 45)->nullable();
            $table->string('response_user_agent', 500)->nullable();
            $table->json('granted_purposes')->nullable();
            $table->string('decline_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parental_consent_requests');
        Schema::dropIfExists('policy_acceptances');
        Schema::dropIfExists('consent_records');
        Schema::dropIfExists('policy_documents');
        Schema::dropIfExists('consent_purposes');
    }
};
