<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name', 150);
            $table->string('registration_number', 50);
            $table->string('provider_code', 30)->unique();
            $table->uuid('default_branch_id');
            $table->string('status', 20)->default('draft');
            $table->string('preferred_payout_method', 20)->default('bank');
            $table->string('external_reference', 100)->nullable();
            $table->string('legacy_reference', 100)->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('default_branch_id')->references('id')->on('branches')->onDelete('restrict');

            $table->unique(['registration_number', 'deleted_at']);
            $table->index('status');
        });

        Schema::create('provider_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->uuid('country_id');
            $table->uuid('state_id');
            $table->uuid('district_id')->nullable();
            $table->uuid('city_id');
            $table->uuid('pincode_id')->nullable();
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_primary')->default(true);

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('pincode_id')->references('id')->on('pincodes')->onDelete('cascade');
        });

        Schema::create('provider_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->string('contact_name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->string('type', 20)->default('owner'); // owner, accounts, operations, sales, emergency

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->unique(['provider_id', 'email', 'type', 'deleted_at']);
        });

        Schema::create('provider_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->string('document_type', 30);
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->integer('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->uuid('replaced_by')->nullable();
            $table->uuid('supersedes')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('replaced_by')->references('id')->on('provider_documents')->onDelete('restrict');
            $table->foreign('supersedes')->references('id')->on('provider_documents')->onDelete('restrict');
        });

        Schema::create('provider_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->uuid('user_id');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['provider_id', 'user_id', 'deleted_at']);
        });

        Schema::create('provider_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->uuid('subscription_plan_id')->nullable();
            $table->integer('max_active_screens');
            $table->string('billing_cycle', 20)->default('monthly'); // monthly, quarterly, yearly
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
        });

        Schema::create('provider_bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->string('bank_name', 100);
            $table->string('account_holder', 150);
            $table->string('account_number', 50);
            $table->string('routing_code', 30);
            $table->boolean('is_primary')->default(true);
            $table->string('verification_status', 20)->default('pending'); // pending, verified, rejected
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_reference', 100)->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('provider_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->json('settings');

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
        });

        Schema::create('provider_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->string('activity_type', 50);
            $table->text('description');
            
            // Context identifiers
            $table->uuid('causation_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->uuid('trace_id')->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_activities');
        Schema::dropIfExists('provider_settings');
        Schema::dropIfExists('provider_bank_accounts');
        Schema::dropIfExists('provider_subscriptions');
        Schema::dropIfExists('provider_staff');
        Schema::dropIfExists('provider_documents');
        Schema::dropIfExists('provider_contacts');
        Schema::dropIfExists('provider_addresses');
        Schema::dropIfExists('providers');
    }
};
