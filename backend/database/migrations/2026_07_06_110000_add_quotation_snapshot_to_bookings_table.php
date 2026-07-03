<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('quotation_id')->nullable()->comment('Link to originating CRM quotation.');
            $table->uuid('quotation_version_id')->nullable()->comment('Link to originating CRM quotation version.');
            $table->timestamp('converted_from_quotation_at')->nullable()->comment('Timestamp of lead qualification / quote conversion.');
            $table->json('booking_snapshot')->nullable()->comment('Immutable copy of final booking parameters.');
            $table->json('quotation_snapshot')->nullable()->comment('Immutable copy of quotation pricing and items details during conversion.');

            $table->foreign('quotation_id')->references('id')->on('crm_quotations')->onDelete('set null');
            $table->foreign('quotation_version_id')->references('id')->on('crm_quotation_versions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropForeign(['quotation_version_id']);
            $table->dropColumn([
                'quotation_id',
                'quotation_version_id',
                'converted_from_quotation_at',
                'booking_snapshot',
                'quotation_snapshot',
            ]);
        });
    }
};
