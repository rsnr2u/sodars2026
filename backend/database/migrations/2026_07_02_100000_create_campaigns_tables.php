<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Campaigns ────────────────────────────────────────────
        Schema::create('campaigns', function (Blueprint $table) {
            $table->comment('Central campaign records tracking ad execution flights.');
            $table->uuid('id')->primary();
            $table->char('booking_id', 36)->nullable()->comment('Linked commercial booking (nullable until booking confirmed).');
            $table->char('customer_id', 36)->comment('Advertiser / customer user reference.');
            $table->char('branch_id', 36)->comment('Governing branch.');
            $table->string('campaign_code', 30)->unique()->comment('Human-readable code (CMP-000001).');
            $table->string('name', 150)->comment('Campaign display name.');
            $table->text('description')->nullable()->comment('Campaign brief or objectives.');
            $table->date('start_date')->comment('Flight start date.');
            $table->date('end_date')->comment('Flight end date.');
            $table->string('status', 30)->default('draft')->comment('Lifecycle: draft, artwork_pending, scheduled, running, paused, completed, archived.');
            $table->json('objectives')->nullable()->comment('Campaign objectives and KPIs.');
            $table->bigInteger('budget_cents')->nullable()->comment('Total budget in smallest currency unit.');
            $table->string('currency', 3)->default('INR')->comment('ISO 4217 currency code.');

            // Audit columns
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('customer_id', 'cmp_customer_fk')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('branch_id', 'cmp_branch_fk')->references('id')->on('branches')->restrictOnDelete();

            // Indexes
            $table->index(['status'], 'cmp_status_idx');
            $table->index(['customer_id'], 'cmp_customer_idx');
            $table->index(['branch_id'], 'cmp_branch_idx');
            $table->index(['start_date', 'end_date'], 'cmp_dates_idx');
        });

        // ─── 2. Campaign Inventory Pivot ─────────────────────────────
        Schema::create('campaign_inventory', function (Blueprint $table) {
            $table->comment('Pivot mapping campaigns to target inventory faces.');
            $table->uuid('id')->primary();
            $table->char('campaign_id', 36);
            $table->char('inventory_face_id', 36)->comment('Bookable unit reference.');
            $table->timestamps();

            $table->foreign('campaign_id', 'ci_campaign_fk')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('inventory_face_id', 'ci_face_fk')->references('id')->on('inventory_faces')->restrictOnDelete();

            $table->unique(['campaign_id', 'inventory_face_id'], 'ci_campaign_face_uniq');
        });

        // ─── 3. Campaign Creatives ───────────────────────────────────
        Schema::create('campaign_creatives', function (Blueprint $table) {
            $table->comment('Creative artwork files uploaded by advertisers.');
            $table->uuid('id')->primary();
            $table->char('campaign_id', 36);
            $table->string('file_name', 150)->comment('Original uploaded filename.');
            $table->string('file_path', 500)->comment('Storage path or S3 key.');
            $table->string('file_type', 30)->comment('Format: JPG, PNG, PDF, AI, PSD, CDR, ZIP, MP4.');
            $table->bigInteger('file_size_bytes')->nullable()->comment('File size in bytes.');
            $table->unsignedSmallInteger('version')->default(1)->comment('Creative version number.');
            $table->string('status', 20)->default('pending')->comment('Audit state: pending, approved, rejected.');
            $table->text('rejection_reason')->nullable()->comment('Auditor feedback if rejected.');
            $table->char('reviewed_by', 36)->nullable()->comment('Branch manager who reviewed.');
            $table->timestamp('reviewed_at')->nullable();

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id', 'cc_campaign_fk')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->index(['campaign_id', 'status'], 'cc_campaign_status_idx');
        });

        // ─── 4. Campaign Schedule ────────────────────────────────────
        Schema::create('campaign_schedule', function (Blueprint $table) {
            $table->comment('Calendar grid mapping loop slot indexes per day per face.');
            $table->uuid('id')->primary();
            $table->char('campaign_id', 36);
            $table->char('inventory_face_id', 36);
            $table->date('date')->comment('Scheduled active date.');
            $table->unsignedSmallInteger('slot_index')->comment('Display loop index (e.g. 1 to 6).');
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id', 'cs_campaign_fk')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('inventory_face_id', 'cs_face_fk')->references('id')->on('inventory_faces')->restrictOnDelete();

            $table->unique(['inventory_face_id', 'date', 'slot_index'], 'cs_face_date_slot_uniq');
        });

        // ─── 5. Campaign Proofs ──────────────────────────────────────
        Schema::create('campaign_proofs', function (Blueprint $table) {
            $table->comment('Proof-of-execution visual evidence uploaded by providers.');
            $table->uuid('id')->primary();
            $table->char('campaign_id', 36);
            $table->char('inventory_face_id', 36)->nullable();
            $table->string('file_path', 500)->comment('S3 verification file path.');
            $table->text('notes')->nullable()->comment('Execution notes.');
            $table->char('uploaded_by', 36)->comment('Provider staff user ID.');
            $table->string('status', 20)->default('pending')->comment('Verification: pending, verified, rejected.');
            $table->char('verified_by', 36)->nullable()->comment('Branch manager who verified.');
            $table->timestamp('verified_at')->nullable();
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id', 'cp_campaign_fk')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('uploaded_by', 'cp_uploader_fk')->references('id')->on('users')->restrictOnDelete();
            $table->index(['campaign_id', 'status'], 'cp_campaign_status_idx');
        });

        // ─── 6. Campaign Notes ───────────────────────────────────────
        Schema::create('campaign_notes', function (Blueprint $table) {
            $table->comment('Operational communication threads on campaigns.');
            $table->uuid('id')->primary();
            $table->char('campaign_id', 36);
            $table->char('user_id', 36)->comment('Author user ID.');
            $table->text('body')->comment('Note content.');
            $table->boolean('is_internal')->default(false)->comment('Internal staff note vs customer-visible.');
            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id', 'cn_campaign_fk')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('user_id', 'cn_user_fk')->references('id')->on('users')->restrictOnDelete();
            $table->index(['campaign_id'], 'cn_campaign_idx');
        });

        // ─── 7. Campaign Activities ──────────────────────────────────
        Schema::create('campaign_activities', function (Blueprint $table) {
            $table->comment('Business timeline audit logs for campaign mutations.');
            $table->uuid('id')->primary();
            $table->char('campaign_id', 36);
            $table->char('performed_by', 36)->nullable();
            $table->string('event_name', 100)->comment('CloudEvents event type.');
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

            $table->foreign('campaign_id', 'ca_campaign_fk')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->index(['campaign_id'], 'ca_campaign_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_activities');
        Schema::dropIfExists('campaign_notes');
        Schema::dropIfExists('campaign_proofs');
        Schema::dropIfExists('campaign_schedule');
        Schema::dropIfExists('campaign_creatives');
        Schema::dropIfExists('campaign_inventory');
        Schema::dropIfExists('campaigns');
    }
};
