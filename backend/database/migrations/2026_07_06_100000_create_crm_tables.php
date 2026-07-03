<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. CRM Accounts (Corporate client profile)
        Schema::create('crm_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('industry', 50)->nullable();
            $table->string('website', 100)->nullable();
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. CRM Contacts (Permanent contact person linked to Account)
        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100);
            $table->string('phone', 30);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('crm_accounts')->onDelete('cascade');
        });

        // 3. CRM Pipeline Stages (Configurable deal stages)
        Schema::create('crm_pipeline_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50);
            $table->integer('display_order')->default(0);
            $table->integer('probability')->default(0); // 0-100%
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_won')->default(false);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. CRM Lost Reasons (Deal lost reasons taxonomy)
        Schema::create('crm_lost_reasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. CRM Leads (Temporary lead profile before qualification)
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->nullable();
            $table->uuid('contact_id')->nullable();
            $table->string('title', 150);
            $table->string('source', 50)->default('website'); // website, phone, walk_in, referral
            $table->string('status', 30)->default('new'); // new, contacted, qualified, lost
            $table->integer('lead_score')->default(0);
            $table->uuid('assigned_to')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('crm_accounts')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('crm_contacts')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // 6. CRM Opportunities (Aggregate Root tracking deals forecast)
        Schema::create('crm_opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('contact_id')->nullable();
            $table->string('title', 150);
            $table->bigInteger('estimated_value_cents')->default(0);
            $table->integer('probability')->default(0); // 0-100%
            $table->bigInteger('expected_value_cents')->default(0);

            $table->uuid('pipeline_stage_id');
            $table->uuid('lost_reason_id')->nullable();
            $table->date('close_date');
            $table->uuid('assigned_to')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('crm_accounts')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('crm_contacts')->onDelete('set null');
            $table->foreign('pipeline_stage_id')->references('id')->on('crm_pipeline_stages')->onDelete('restrict');
            $table->foreign('lost_reason_id')->references('id')->on('crm_lost_reasons')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // 7. CRM Quotations (Header aggregate root)
        Schema::create('crm_quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('opportunity_id')->nullable();
            $table->uuid('account_id');
            $table->string('quotation_number', 50)->unique();
            $table->string('status', 30)->default('draft'); // draft, under_review, approved, sent, accepted, expired, rejected
            $table->integer('active_version_number')->default(1);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('opportunity_id')->references('id')->on('crm_opportunities')->onDelete('set null');
            $table->foreign('account_id')->references('id')->on('crm_accounts')->onDelete('cascade');
        });

        // 8. CRM Quotation Versions (Offers history tracker)
        Schema::create('crm_quotation_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quotation_id');
            $table->integer('version_number')->default(1);
            $table->date('valid_until');
            $table->bigInteger('subtotal_cents')->default(0);
            $table->bigInteger('discount_cents')->default(0);
            $table->bigInteger('tax_cents')->default(0);
            $table->bigInteger('grand_total_cents')->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('quotation_id')->references('id')->on('crm_quotations')->onDelete('cascade');
        });

        // 9. CRM Quotation Items (Lines linked to specific versions)
        Schema::create('crm_quotation_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('quotation_version_id');
            $table->uuid('inventory_face_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('daily_frequency')->default(1);
            $table->bigInteger('price_cents')->default(0);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('quotation_version_id')->references('id')->on('crm_quotation_versions')->onDelete('cascade');
            $table->foreign('inventory_face_id')->references('id')->on('inventory_faces')->onDelete('restrict');
        });

        // 10. CRM Followups (Reminders and recurrences)
        Schema::create('crm_followups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_id')->nullable();
            $table->uuid('opportunity_id')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->text('task_description');
            $table->string('recurrence', 30)->default('once');
            $table->timestamp('due_at');
            $table->string('status', 30)->default('pending'); // pending, completed, overdue
            $table->timestamp('completed_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_id')->references('id')->on('crm_leads')->onDelete('cascade');
            $table->foreign('opportunity_id')->references('id')->on('crm_opportunities')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // 11. CRM Activities (Polymorphic logging audit trails)
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('activityable_type', 100);
            $table->uuid('activityable_id');
            $table->uuid('performed_by')->nullable();
            $table->string('activity_type', 50); // email, call, meeting, note, stage_change, status_change
            $table->text('description');
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['activityable_type', 'activityable_id'], 'activityable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_followups');
        Schema::dropIfExists('crm_quotation_items');
        Schema::dropIfExists('crm_quotation_versions');
        Schema::dropIfExists('crm_quotations');
        Schema::dropIfExists('crm_opportunities');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_lost_reasons');
        Schema::dropIfExists('crm_pipeline_stages');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_accounts');
    }
};
