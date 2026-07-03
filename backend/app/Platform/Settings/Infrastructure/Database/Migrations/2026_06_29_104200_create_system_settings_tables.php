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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->comment('Global configuration settings and variables');
            $table->uuid('id')->primary()->comment('Unique setting UUID');
            $table->string('setting_key')->unique()->comment('Key name of config');
            $table->text('setting_value')->nullable()->comment('Encrypted or plain value');
            $table->string('group_name')->comment('Setting group like email, sms');
            $table->string('category')->comment('Category like security, branding');
            $table->boolean('is_encrypted')->default(false)->comment('Flag indicating if value is encrypted');
            $table->boolean('is_env_override')->default(false)->comment('Flag indicating if ENV overrides database value');

            $table->char('created_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('updated_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('deleted_by', 36)->nullable()->comment('Modifier user reference');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->comment('Company profile configurations');
            $table->uuid('id')->primary()->comment('Unique company profile UUID');
            $table->string('legal_name');
            $table->string('tax_number')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('logo_s3_path')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();

            $table->char('created_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('updated_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('deleted_by', 36)->nullable()->comment('Modifier user reference');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->comment('Feature flag gate flags toggling');
            $table->uuid('id')->primary();
            $table->string('flag_key')->unique();
            $table->boolean('is_enabled')->default(false);
            $table->string('description')->nullable();

            $table->char('created_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('updated_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('deleted_by', 36)->nullable()->comment('Modifier user reference');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('system_backups', function (Blueprint $table) {
            $table->comment('Platform system database backups tracking list');
            $table->uuid('id')->primary();
            $table->string('file_name');
            $table->string('file_path');
            $table->bigInteger('file_size_bytes');

            $table->char('created_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('updated_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('deleted_by', 36)->nullable()->comment('Modifier user reference');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('system_versions', function (Blueprint $table) {
            $table->comment('Platform release version logs');
            $table->uuid('id')->primary();
            $table->string('version_tag')->unique();
            $table->text('release_notes')->nullable();
            $table->timestamp('deployed_at')->nullable();

            $table->char('created_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('updated_by', 36)->nullable()->comment('Modifier user reference');
            $table->char('deleted_by', 36)->nullable()->comment('Modifier user reference');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_versions');
        Schema::dropIfExists('system_backups');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('system_settings');
    }
};
