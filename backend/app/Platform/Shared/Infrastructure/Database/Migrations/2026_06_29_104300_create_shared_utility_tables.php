<?php

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
        // Geography tables
        Schema::create('countries', function (Blueprint $table) {
            $table->comment('Master registry of support countries');
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('iso_code', 3)->unique();
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->comment('Master list of states per country');
            $table->uuid('id')->primary();
            $table->uuid('country_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->comment('Master list of districts per state');
            $table->uuid('id')->primary();
            $table->uuid('state_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->comment('Master list of cities per district');
            $table->uuid('id')->primary();
            $table->uuid('district_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });

        Schema::create('pincodes', function (Blueprint $table) {
            $table->comment('Pincodes index mapping per city');
            $table->uuid('id')->primary();
            $table->uuid('city_id');
            $table->string('code', 10);
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });

        // Media table
        Schema::create('media_library', function (Blueprint $table) {
            $table->comment('Central uploads file media manager table');
            $table->uuid('id')->primary();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size_bytes');

            // Polymorphic links
            $table->string('mediable_type')->nullable();
            $table->uuid('mediable_id')->nullable();
            $table->index(['mediable_id', 'mediable_type']);

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();
            $table->char('deleted_by', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Logs tables
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->comment('Immutable database event logs');
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('event'); // created, updated, deleted
            $table->string('auditable_type');
            $table->uuid('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['auditable_id', 'auditable_type']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->comment('Lightweight audit log actions entries for dashboard tracking');
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('log_message');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Sequences & temporary files
        Schema::create('system_sequences', function (Blueprint $table) {
            $table->comment('Serialized unique sequence keys for booking and invoice ids');
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->bigInteger('current_value')->default(1);
            $table->timestamps();
        });

        Schema::create('temporary_files', function (Blueprint $table) {
            $table->comment('Temporary chunks files queue registries');
            $table->uuid('id')->primary();
            $table->string('file_path');
            $table->timestamp('expiry_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_files');
        Schema::dropIfExists('system_sequences');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('media_library');
        Schema::dropIfExists('pincodes');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
