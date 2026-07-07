<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update wallets table
        Schema::table('wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('wallets', 'organization_id')) {
                $table->uuid('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
            if (!Schema::hasColumn('wallets', 'wallet_number')) {
                $table->string('wallet_number', 50)->nullable()->unique()->after('organization_id');
            }
        });

        // 2. Update wallet_transactions table
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'organization_id')) {
                $table->uuid('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
            if (!Schema::hasColumn('wallet_transactions', 'transaction_reference')) {
                $table->string('transaction_reference', 100)->nullable()->after('organization_id');
            }
            if (!Schema::hasColumn('wallet_transactions', 'sequence_number')) {
                $table->integer('sequence_number')->default(0)->after('transaction_reference');
            }
            if (!Schema::hasColumn('wallet_transactions', 'posting_status')) {
                $table->string('posting_status', 30)->default('posted')->after('sequence_number');
            }
            if (!Schema::hasColumn('wallet_transactions', 'invoice_id')) {
                $table->uuid('invoice_id')->nullable()->after('posting_status');
                $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            }
            if (!Schema::hasColumn('wallet_transactions', 'payment_id')) {
                $table->uuid('payment_id')->nullable()->after('invoice_id');
                $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            }
            if (!Schema::hasColumn('wallet_transactions', 'settlement_id')) {
                $table->uuid('settlement_id')->nullable()->after('payment_id');
                $table->foreign('settlement_id')->references('id')->on('provider_settlements')->nullOnDelete();
            }
        });

        // 3. Update withdrawals table
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'organization_id')) {
                $table->uuid('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
            if (!Schema::hasColumn('withdrawals', 'withdrawal_number')) {
                $table->string('withdrawal_number', 50)->nullable()->unique()->after('organization_id');
            }
        });

        // 4. Update wallet_activities table
        Schema::table('wallet_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_activities', 'organization_id')) {
                $table->uuid('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallet_activities', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_activities', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'withdrawal_number')) {
                $table->dropColumn('withdrawal_number');
            }
            if (Schema::hasColumn('withdrawals', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $cols = ['settlement_id', 'payment_id', 'invoice_id', 'posting_status', 'sequence_number', 'transaction_reference', 'organization_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('wallet_transactions', $col)) {
                    if (in_array($col, ['settlement_id', 'payment_id', 'invoice_id', 'organization_id'], true)) {
                        $table->dropForeign([$col]);
                    }
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('wallets', function (Blueprint $table) {
            if (Schema::hasColumn('wallets', 'wallet_number')) {
                $table->dropColumn('wallet_number');
            }
            if (Schema::hasColumn('wallets', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });
    }
};
