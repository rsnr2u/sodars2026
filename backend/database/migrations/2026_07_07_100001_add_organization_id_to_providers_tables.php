<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'providers',
            'provider_addresses',
            'provider_contacts',
            'provider_documents',
            'provider_staff',
            'provider_subscriptions',
            'provider_bank_accounts',
            'provider_settings',
            'provider_activities',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'organization_id')) {
                    $table->uuid('organization_id')->nullable()->after('id');
                    $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
                }
            });
        }

        Schema::table('provider_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_documents', 'asset_id')) {
                $table->uuid('asset_id')->nullable()->after('provider_id');
                $table->foreign('asset_id')->references('id')->on('dam_assets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('provider_documents', function (Blueprint $table) {
            if (Schema::hasColumn('provider_documents', 'asset_id')) {
                $table->dropForeign(['asset_id']);
                $table->dropColumn('asset_id');
            }
        });

        $tables = [
            'provider_activities',
            'provider_settings',
            'provider_bank_accounts',
            'provider_subscriptions',
            'provider_staff',
            'provider_documents',
            'provider_contacts',
            'provider_addresses',
            'providers',
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
