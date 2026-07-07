<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Accounting Periods
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fiscal_year', 10);
            $table->integer('month');
            $table->string('status', 20)->default('open'); // draft, open, closing, closed, locked

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Ledger Accounts (COA)
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_account_id')->nullable();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->string('type', 20); // asset, liability, equity, revenue, expense
            $table->string('normal_balance', 10); // debit, credit
            $table->boolean('is_control_account')->default(false);
            $table->boolean('allow_manual_posting')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('currency', 3)->default('INR');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_account_id')->references('id')->on('ledger_accounts')->onDelete('cascade');
        });

        // 3. Ledger Journals
        Schema::create('ledger_journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_number', 50)->unique();
            $table->string('narration', 255);
            $table->string('journal_type', 30); // manual, booking, invoice, settlement, wallet, withdrawal, adjustment, reversal
            $table->string('status', 30)->default('posted'); // draft, validated, posted, reversed
            $table->uuid('reversal_of_journal_id')->nullable();
            $table->uuid('accounting_period_id');

            // Metadata
            $table->string('source_module', 50)->nullable();
            $table->string('source_id', 50)->nullable();
            $table->string('source_type', 100)->nullable();
            $table->string('source_event', 100)->nullable();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->uuid('posted_by')->nullable();
            $table->uuid('approved_by')->nullable();

            // Trace Context
            $table->uuid('trace_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->uuid('causation_id')->nullable();

            $table->timestamp('posted_at')->nullable();
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reversal_of_journal_id')->references('id')->on('ledger_journals')->onDelete('set null');
            $table->foreign('accounting_period_id')->references('id')->on('accounting_periods')->onDelete('cascade');
        });

        // 4. Ledger Entries (Journal Lines)
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('journal_id');
            $table->uuid('ledger_account_id');
            $table->integer('line_number');
            $table->string('entry_type', 10); // debit, credit
            $table->bigInteger('amount_cents');
            $table->string('description', 255)->nullable();

            // Multi-currency details
            $table->string('base_currency', 3)->default('INR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->bigInteger('base_amount_cents');

            // Polymorphic source references
            $table->string('ledgerable_type', 150)->nullable();
            $table->char('ledgerable_id', 36)->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('journal_id')->references('id')->on('ledger_journals')->onDelete('cascade');
            $table->foreign('ledger_account_id')->references('id')->on('ledger_accounts')->onDelete('cascade');
            $table->index(['ledgerable_type', 'ledgerable_id'], 'ledgerable_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_journals');
        Schema::dropIfExists('ledger_accounts');
        Schema::dropIfExists('accounting_periods');
    }
};
