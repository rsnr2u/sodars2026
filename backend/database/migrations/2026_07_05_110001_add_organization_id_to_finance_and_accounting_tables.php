<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'invoices',
            'provider_settlements',
            'revenue_recognition_schedules',
            'ledger_accounts',
            'ledger_journals',
            'accounting_periods',
            'payments',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // If column doesn't exist, create it
                if (!Schema::hasColumn($tableName, 'organization_id')) {
                    $table->uuid('organization_id')->nullable()->after('id');
                    $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
                }
            });
        }

        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->unique(['organization_id', 'code']);
        });
    }

    public function down(): void
    {
        $tables = [
            'payments',
            'accounting_periods',
            'ledger_journals',
            'ledger_accounts',
            'revenue_recognition_schedules',
            'provider_settlements',
            'invoices',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'organization_id')) {
                    $table->dropForeign(['organization_id']);
                    $table->dropColumn('organization_id');
                }
            });
        }
    }
};
