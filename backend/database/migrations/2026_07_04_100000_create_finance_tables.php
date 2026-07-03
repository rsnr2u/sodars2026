<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Invoices (Aggregate Root)
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 50)->unique();
            $table->uuid('booking_id')->nullable();
            $table->uuid('customer_id');
            $table->uuid('branch_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->bigInteger('subtotal_cents');
            $table->bigInteger('discount_cents')->default(0);
            $table->bigInteger('tax_cents')->default(0);
            $table->bigInteger('grand_total_cents');
            $table->string('currency', 3);
            $table->string('status', 30);
            $table->string('invoice_type', 30);
            $table->json('booking_snapshot');

            // Audit
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // 2. Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('description', 255);
            $table->integer('quantity');
            $table->bigInteger('unit_price_cents');
            $table->bigInteger('total_price_cents');
            $table->json('pricing_snapshot');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });

        // 3. Invoice Adjustments
        Schema::create('invoice_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('adjustment_type', 30); // credit, debit
            $table->bigInteger('amount_cents');
            $table->string('reason', 255);
            $table->uuid('recorded_by');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });

        // 4. Invoice Taxes
        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('tax_name', 50); // CGST, SGST, IGST
            $table->decimal('tax_rate_percentage', 5, 2);
            $table->bigInteger('tax_amount_cents');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });

        // 5. Invoice Activities
        Schema::create('invoice_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->uuid('performed_by');
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->uuid('trace_id')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });

        // 6. Provider Settlements (Aggregate Root)
        Schema::create('provider_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('settlement_number', 50)->unique();
            $table->uuid('provider_id');
            $table->uuid('booking_id');
            $table->uuid('invoice_id');
            $table->bigInteger('total_amount_cents');
            $table->bigInteger('provider_share_cents');
            $table->bigInteger('commission_cents');
            $table->bigInteger('tax_cents')->default(0);
            $table->string('status', 30);
            $table->timestamp('processed_at')->nullable();
            $table->string('payout_reference', 100)->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });

        // 7. Provider Settlement Items
        Schema::create('provider_settlement_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_settlement_id');
            $table->uuid('booking_item_id');
            $table->bigInteger('amount_cents');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_settlement_id')->references('id')->on('provider_settlements')->onDelete('cascade');
        });

        // 8. Provider Settlement Adjustments
        Schema::create('provider_settlement_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_settlement_id');
            $table->string('adjustment_type', 30);
            $table->bigInteger('amount_cents');
            $table->string('reason', 255);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_settlement_id')->references('id')->on('provider_settlements')->onDelete('cascade');
        });

        // 9. Provider Settlement Activities
        Schema::create('provider_settlement_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_settlement_id');
            $table->uuid('performed_by');
            $table->string('action', 50);
            $table->text('description')->nullable();
            $table->uuid('trace_id')->nullable();
            $table->timestamps();

            $table->foreign('provider_settlement_id')->references('id')->on('provider_settlements')->onDelete('cascade');
        });

        // 10. Revenue Recognition Schedules
        Schema::create('revenue_recognition_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('booking_id');
            $table->uuid('booking_item_id');
            $table->date('recognition_date');
            $table->bigInteger('amount_cents');
            $table->string('status', 30); // pending, completed

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });

        // 11. Revenue Recognition Entries
        Schema::create('revenue_recognition_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schedule_id');
            $table->date('recognition_date');
            $table->bigInteger('amount_cents');
            $table->string('status', 30); // recognized, deferred

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('revenue_recognition_schedules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_recognition_entries');
        Schema::dropIfExists('revenue_recognition_schedules');
        Schema::dropIfExists('provider_settlement_activities');
        Schema::dropIfExists('provider_settlement_adjustments');
        Schema::dropIfExists('provider_settlement_items');
        Schema::dropIfExists('provider_settlements');
        Schema::dropIfExists('invoice_activities');
        Schema::dropIfExists('invoice_taxes');
        Schema::dropIfExists('invoice_adjustments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
