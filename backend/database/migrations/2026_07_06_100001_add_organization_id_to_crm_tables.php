<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'crm_accounts',
            'crm_contacts',
            'crm_leads',
            'crm_opportunities',
            'crm_quotations',
            'crm_followups',
            'crm_activities',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->uuid('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'crm_activities',
            'crm_followups',
            'crm_quotations',
            'crm_opportunities',
            'crm_leads',
            'crm_contacts',
            'crm_accounts',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};
