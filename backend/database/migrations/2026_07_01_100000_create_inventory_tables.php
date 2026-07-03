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
        Schema::create('inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('inventory_code', 30);
            $table->string('display_name', 150);
            $table->uuid('provider_id');
            $table->uuid('branch_id');
            
            // Geography keys
            $table->uuid('country_id');
            $table->uuid('state_id');
            $table->uuid('district_id')->nullable();
            $table->uuid('city_id');
            $table->uuid('pincode_id')->nullable();

            $table->string('inventory_category', 50);
            $table->string('inventory_type', 50);
            $table->string('ownership_type', 50);

            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('geo_hash', 12);
            $table->string('normalized_address', 255);
            $table->text('search_keywords')->nullable();
            $table->text('search_vector')->nullable();

            $table->string('status', 20)->default('draft');
            $table->boolean('marketplace_enabled')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('accepts_programmatic_booking')->default(false);
            $table->string('visibility', 20)->default('public');

            // JSON value object fields
            $table->json('ai_scores');
            $table->json('inventory_capabilities');
            $table->timestamp('last_ai_analysis_at')->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('restrict');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('restrict');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('restrict');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('restrict');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('restrict');
            $table->foreign('pincode_id')->references('id')->on('pincodes')->onDelete('restrict');

            $table->unique(['inventory_code', 'deleted_at']);

            // Composite indexes
            $table->index(['provider_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['city_id', 'inventory_type']);
            $table->index(['inventory_category', 'inventory_type']);
            $table->index(['marketplace_enabled', 'status']);
            $table->index(['status', 'marketplace_enabled']);
            $table->index('geo_hash');
        });

        Schema::create('inventory_faces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->string('face_code', 50);
            $table->string('display_name', 100);
            $table->string('facing_direction', 20);
            $table->integer('display_order')->default(1);
            $table->json('physical_specifications');
            $table->boolean('is_active')->default(true);

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');

            $table->index(['inventory_id', 'display_order']);
            $table->index(['inventory_id', 'face_code']);
            $table->index(['inventory_id', 'is_active']);
        });

        Schema::create('inventory_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->uuid('media_id');
            $table->string('media_type', 30);
            $table->integer('display_order')->default(1);
            $table->boolean('is_primary')->default(false);

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media_library')->onDelete('cascade');
        });

        Schema::create('inventory_pricing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_face_id');
            $table->string('pricing_type', 30)->default('baseline');
            $table->integer('rate_cents');
            $table->string('currency', 10)->default('INR');
            $table->boolean('tax_inclusive')->default(false);
            $table->integer('minimum_booking_days')->default(1);
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->integer('priority')->default(0);

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_face_id')->references('id')->on('inventory_faces')->onDelete('cascade');

            $table->index(['inventory_face_id', 'effective_from'], 'inv_price_eff_from_idx');
            $table->index(['inventory_face_id', 'effective_to'], 'inv_price_eff_to_idx');
            $table->index(['inventory_face_id', 'priority'], 'inv_price_priority_idx');
        });

        Schema::create('inventory_availability', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_face_id');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('availability_status', 30);
            $table->string('reason', 50);
            $table->string('source', 30);
            $table->text('remarks')->nullable();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_face_id')->references('id')->on('inventory_faces')->onDelete('cascade');

            $table->index(['inventory_face_id', 'start_at'], 'inv_avail_start_idx');
            $table->index(['inventory_face_id', 'end_at'], 'inv_avail_end_idx');
            $table->index(['inventory_face_id', 'availability_status'], 'inv_avail_status_idx');
        });

        Schema::create('inventory_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->string('document_type', 50);
            $table->string('status', 20)->default('pending');

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
        });

        Schema::create('inventory_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique();
        });

        Schema::create('inventory_taggables', function (Blueprint $table) {
            $table->uuid('tag_id');
            $table->uuid('taggable_id');
            $table->string('taggable_type', 150);

            $table->foreign('tag_id')->references('id')->on('inventory_tags')->onDelete('cascade');
            $table->index(['taggable_id', 'taggable_type']);
        });

        Schema::create('inventory_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->uuid('performed_by')->nullable();
            $table->string('event_name', 100);
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->uuid('trace_id')->nullable();

            $table->timestamps();

            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_activities');
        Schema::dropIfExists('inventory_taggables');
        Schema::dropIfExists('inventory_tags');
        Schema::dropIfExists('inventory_documents');
        Schema::dropIfExists('inventory_availability');
        Schema::dropIfExists('inventory_pricing');
        Schema::dropIfExists('inventory_media');
        Schema::dropIfExists('inventory_faces');
        Schema::dropIfExists('inventories');
    }
};
