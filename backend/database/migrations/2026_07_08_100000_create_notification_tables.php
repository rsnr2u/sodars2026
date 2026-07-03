<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Configurable Channels
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 50)->unique(); // email, sms, whatsapp, push, in_app
            $table->string('driver', 50); // e.g. smtp, twilio, msg91, fcm, local
            $table->boolean('is_enabled')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('retry_attempts')->default(3);
            $table->integer('timeout_seconds')->default(30);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        // 2. Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 100)->unique(); // booking.confirmed, invoice.paid, etc.
            $table->string('name', 150);
            $table->string('category', 50)->default('transactional'); // transactional, marketing, system, security, finance, crm, campaign
            $table->integer('active_version_number')->default(1);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Template Versions
        Schema::create('notification_template_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->integer('version_number');
            $table->string('subject', 200)->nullable();
            $table->json('content')->nullable(); // channel -> {title, subtitle, body, image_url, button_text, button_url}
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('cascade');
        });

        // Add foreign key constraint to template versions pointer
        Schema::table('notification_templates', function (Blueprint $table) {
            // Keep active_version pointer loose to avoid migrations cycles or loop FKs
        });

        // 4. Notification Dispatches (Aggregate Root)
        Schema::create('notification_dispatches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->nullable();
            $table->uuid('template_version_id')->nullable();
            $table->uuid('recipient_id')->nullable(); // links to users
            $table->string('recipient_contact', 100); // email address, phone, token
            $table->string('channel', 50); // email, sms, etc.
            $table->string('status', 30)->default('draft'); // draft, queued, processing, sent, delivered, read, expired, cancelled, failed
            $table->json('context_snapshot')->nullable();
            
            $table->timestamp('send_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
            $table->foreign('template_version_id')->references('id')->on('notification_template_versions')->onDelete('set null');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('set null');
        });

        // 5. Notification Delivery Attempts
        Schema::create('notification_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dispatch_id');
            $table->integer('attempt_number')->default(1);
            
            $table->string('provider_name', 50)->nullable();
            $table->string('provider_reference', 150)->nullable();
            $table->text('provider_response')->nullable();
            $table->integer('provider_status_code')->nullable();

            $table->string('status', 30); // sent, failed, retrying
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('dispatch_id')->references('id')->on('notification_dispatches')->onDelete('cascade');
        });

        // 6. User Preferences
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('category', 50);
            $table->string('channel', 50);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'category', 'channel'], 'pref_user_cat_chan_uniq');
        });

        // 7. Database Feed In-App Alerts
        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('dispatch_id')->nullable();
            $table->string('title', 150);
            $table->text('message');
            $table->string('type', 30)->default('info'); // info, success, warning, danger
            $table->string('link_url', 255)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dispatch_id')->references('id')->on('notification_dispatches')->onDelete('set null');
        });

        // 8. Attachments Linkage to DAM Platform assets
        Schema::create('notification_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dispatch_id');
            $table->uuid('asset_id');
            $table->timestamps();

            $table->foreign('dispatch_id')->references('id')->on('notification_dispatches')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_attachments');
        Schema::dropIfExists('in_app_notifications');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_attempts');
        Schema::dropIfExists('notification_dispatches');
        Schema::dropIfExists('notification_template_versions');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_channels');
    }
};
