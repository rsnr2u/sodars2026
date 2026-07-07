<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'campaigns',
            'campaign_creatives',
            'campaign_schedule',
            'campaign_proofs',
            'campaign_notes',
            'campaign_activities',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'organization_id')) {
                    $table->uuid('organization_id')->nullable()->after('id');
                    $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
                }
            });
        }

        // Add Campaign budget variance columns
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'planned_budget_cents')) {
                $table->bigInteger('planned_budget_cents')->default(0)->after('budget_cents');
                $table->bigInteger('approved_budget_cents')->default(0)->after('planned_budget_cents');
                $table->bigInteger('actual_spend_cents')->default(0)->after('approved_budget_cents');
                $table->bigInteger('remaining_budget_cents')->default(0)->after('actual_spend_cents');
            }
        });

        // Add asset_id to campaign_creatives & campaign_proofs
        Schema::table('campaign_creatives', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_creatives', 'asset_id')) {
                $table->uuid('asset_id')->nullable()->after('campaign_id');
                $table->foreign('asset_id')->references('id')->on('dam_assets')->nullOnDelete();
            }
        });

        Schema::table('campaign_proofs', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_proofs', 'asset_id')) {
                $table->uuid('asset_id')->nullable()->after('campaign_id');
                $table->foreign('asset_id')->references('id')->on('dam_assets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_proofs', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_proofs', 'asset_id')) {
                $table->dropForeign(['asset_id']);
                $table->dropColumn('asset_id');
            }
        });

        Schema::table('campaign_creatives', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_creatives', 'asset_id')) {
                $table->dropForeign(['asset_id']);
                $table->dropColumn('asset_id');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $cols = ['planned_budget_cents', 'approved_budget_cents', 'actual_spend_cents', 'remaining_budget_cents'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('campaigns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        $tables = [
            'campaign_activities',
            'campaign_notes',
            'campaign_proofs',
            'campaign_schedule',
            'campaign_creatives',
            'campaigns',
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
