<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Bookings ─────────────────────────────────────────────
        Schema::create('bookings', function (Blueprint $table) {
            $table->comment('Primary transaction booking records tracking totals and state.');
            $table->uuid('id')->primary();
            $table->char('booking_code', 30)->unique()->comment('Sequential code BK-YYYY-XXXXXX.');
            $table->char('customer_id', 36);
            $table->char('branch_id', 36);
            $table->date('start_date')->comment('Calculated overall flight start date.');
            $table->date('end_date')->comment('Calculated overall flight end date.');
            
            // Financials (in cents/paisa)
            $table->bigInteger('subtotal_cents')->default(0);
            $table->bigInteger('discount_cents')->default(0);
            $table->bigInteger('tax_cents')->default(0);
            $table->bigInteger('platform_fee_cents')->default(0);
            $table->bigInteger('provider_share_cents')->default(0);
            $table->bigInteger('commission_cents')->default(0);
            $table->bigInteger('grand_total_cents')->default(0);
            $table->string('currency', 3)->default('INR');

            $table->string('status', 30)->default('draft')->comment('draft, submitted, branch_review, provider_review, approved, scheduled, active, completed, cancelled, expired, rejected');

            // Audit
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();

            $table->index(['status'], 'bk_status_idx');
            $table->index(['customer_id'], 'bk_customer_idx');
            $table->index(['branch_id'], 'bk_branch_idx');
        });

        // ─── 2. Booking Items ────────────────────────────────────────
        Schema::create('booking_items', function (Blueprint $table) {
            $table->comment('Individual faces reserved under a specific booking transaction.');
            $table->uuid('id')->primary();
            $table->char('booking_id', 36);
            $table->char('inventory_face_id', 36)->comment('Target reserved bookable unit.');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('daily_frequency')->default(1);

            // Copied details at checkout
            $table->bigInteger('net_price_cents');
            $table->unsignedInteger('markup_percentage')->default(0);
            $table->bigInteger('retail_price_cents');
            $table->bigInteger('total_item_price_cents');
            
            // Pricing snapshot VO
            $table->json('pricing_snapshot')->comment('Immutable JSON layout tracking fee details.');

            // Audit
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('inventory_face_id')->references('id')->on('inventory_faces')->restrictOnDelete();

            $table->index(['booking_id'], 'bi_booking_idx');
            $table->index(['inventory_face_id'], 'bi_face_idx');
        });

        // ─── 3. Booking Status History ────────────────────────────────
        Schema::create('booking_status_history', function (Blueprint $table) {
            $table->comment('Audit logs tracking state transitions and remarks.');
            $table->uuid('id')->primary();
            $table->char('booking_id', 36);
            $table->char('changed_by', 36);
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->text('comment')->nullable();

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->restrictOnDelete();
        });

        // ─── 4. Booking Documents ─────────────────────────────────────
        Schema::create('booking_documents', function (Blueprint $table) {
            $table->comment('Payment proofs, receipt screenshots, or system invoice PDFs.');
            $table->uuid('id')->primary();
            $table->char('booking_id', 36);
            $table->string('doc_type', 50)->comment('payment_receipt, invoice_pdf');
            
            // Polymorphic link to MediaLibrary
            $table->string('file_path', 500);

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
        });

        // ─── 5. Booking Notes ─────────────────────────────────────────
        Schema::create('booking_notes', function (Blueprint $table) {
            $table->comment('Discussion threads mapping internal and customer feedback.');
            $table->uuid('id')->primary();
            $table->char('booking_id', 36);
            $table->char('author_id', 36);
            $table->text('note_text');
            $table->boolean('is_internal')->default(false);

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->restrictOnDelete();
        });

        // ─── 6. Payments (Polymorphic) ───────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->comment('Polymorphic payment ledger mapping offline transactions.');
            $table->uuid('id')->primary();
            
            // Polymorphic columns
            $table->char('paymentable_id', 36);
            $table->string('paymentable_type', 150);

            $table->string('payment_method', 30)->comment('cash, bank_transfer, cheque, upi, neft, rtgs');
            $table->bigInteger('amount_cents');
            $table->string('reference_number', 100);
            $table->string('status', 20)->default('pending')->comment('pending, verified, failed');
            $table->char('recorded_by', 36);

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('recorded_by')->references('id')->on('users')->restrictOnDelete();
            $table->index(['paymentable_id', 'paymentable_type'], 'pm_polymorphic_idx');
        });

        // ─── 7. Booking Activities ────────────────────────────────────
        Schema::create('booking_activities', function (Blueprint $table) {
            $table->comment('Timeline activities log for audit reviews.');
            $table->uuid('id')->primary();
            $table->char('booking_id', 36);
            $table->char('performed_by', 36)->nullable();
            $table->string('event_name', 100);
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->char('trace_id', 36)->nullable();

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->index(['booking_id'], 'ba_booking_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_activities');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_notes');
        Schema::dropIfExists('booking_documents');
        Schema::dropIfExists('booking_status_history');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
    }
};
