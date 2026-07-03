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
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('aggregate_type');
            $table->string('aggregate_id');
            $table->string('event_name');
            $table->integer('event_version')->default(1);
            $table->string('schema_version')->default('1.0.0');
            $table->json('payload');
            $table->json('headers');
            $table->uuid('correlation_id');
            $table->uuid('causation_id');
            $table->uuid('trace_id');
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->timestamp('available_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Indexes for queries & cleanup
            $table->index(['status', 'available_at']);
            $table->index(['status', 'created_at']);
            $table->index('correlation_id');
            $table->index('trace_id');
            $table->index(['aggregate_type', 'aggregate_id']);
        });

        Schema::create('inbox_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source');
            $table->string('message_id');
            $table->string('event_name');
            $table->json('payload');
            $table->string('status')->default('received');
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Unique index for deduplication
            $table->unique(['source', 'message_id']);
            $table->index('status');
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox_events');
        Schema::dropIfExists('outbox_events');
    }
};
