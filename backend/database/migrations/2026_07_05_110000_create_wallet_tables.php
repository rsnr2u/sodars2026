<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Wallets
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Polymorphic wallet owner
            $table->string('holder_type', 150);
            $table->char('holder_id', 36);

            $table->uuid('ledger_account_id');
            $table->string('wallet_type', 30); // provider, customer, branch, corporate, escrow, system
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('active'); // active, suspended

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ledger_account_id')->references('id')->on('ledger_accounts')->onDelete('cascade');
            $table->index(['holder_type', 'holder_id'], 'wallet_holder_idx');
        });

        // 2. Wallet Transactions (Snapshots for UI/Diagnostics)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->uuid('ledger_journal_id');
            $table->bigInteger('amount_cents');
            $table->bigInteger('running_balance_snapshot');
            $table->string('type', 30); // deposit, withdrawal, transfer, settlement, refund, adjustment, commission, reversal, correction
            $table->string('status', 20)->default('completed'); // pending, completed, failed
            $table->string('reference_number', 100);
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->foreign('ledger_journal_id')->references('id')->on('ledger_journals')->onDelete('cascade');
        });

        // 3. Withdrawals requests
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->bigInteger('amount_cents');
            $table->json('bank_account_details');
            $table->string('status', 20); // requested, under_review, approved, processing, completed, rejected, failed, cancelled
            $table->string('payout_reference', 100)->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });

        // 4. Wallet Activities
        Schema::create('wallet_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->uuid('performed_by');
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->uuid('trace_id')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_activities');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
