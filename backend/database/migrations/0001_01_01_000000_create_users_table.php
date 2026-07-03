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
        Schema::create('users', function (Blueprint $table) {
            $table->comment('SODARS core system users table storing auth details');
            $table->uuid('id')->primary()->comment('Unique user identifier UUID v4');
            $table->string('name')->comment('Full name of the user');
            $table->string('email')->unique()->comment('Unique email address used for login');
            $table->timestamp('email_verified_at')->nullable()->comment('Timestamp of email verification');
            $table->string('password')->comment('Bcrypt hashed login password');
            $table->rememberToken()->comment('Remember me session token');

            // Modifier & Audit Columns
            $table->char('created_by', 36)->nullable()->comment('User ID who created this record');
            $table->char('updated_by', 36)->nullable()->comment('User ID who last updated this record');
            $table->char('deleted_by', 36)->nullable()->comment('User ID who soft-deleted this record');

            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->comment('Password reset request verification tokens registry');
            $table->string('email')->primary()->comment('Email reference');
            $table->string('token')->comment('Hashed reset verification token');
            $table->timestamp('created_at')->nullable()->comment('Creation timestamp');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->comment('Core web login session storage');
            $table->string('id')->primary()->comment('Unique session identifier');
            $table->char('user_id', 36)->nullable()->index()->comment('User ID reference link');
            $table->string('ip_address', 45)->nullable()->comment('IP address of connected client');
            $table->text('user_agent')->nullable()->comment('Browser user agent details');
            $table->longText('payload')->comment('Serialized session data parameters');
            $table->integer('last_activity')->index()->comment('Unix timestamp of last user action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
