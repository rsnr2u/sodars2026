<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Automation Rules
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('key', 100); // e.g. booking.auto_approval
            $table->integer('version')->default(1);
            $table->string('event_class'); // FQCN of the event (e.g. App\Modules\Bookings\Domain\Events\BookingCreated)
            $table->json('conditions')->nullable(); // Expression tree JSON
            $table->json('actions')->nullable(); // Array of action definitions
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['key', 'version']);
        });

        // 2. Automation Executions
        Schema::create('automation_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rule_id');
            $table->string('event_name', 150);
            $table->json('context_snapshot')->nullable();
            $table->string('status', 50)->default('success'); // success, failed, skipped
            $table->integer('execution_time_ms')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('rule_id')->references('id')->on('automation_rules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_executions');
        Schema::dropIfExists('automation_rules');
    }
};
