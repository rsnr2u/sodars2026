<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('search_indexes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('entity_type');
            $table->string('provider')->default('mysql');
            $table->json('field_mappings');
            $table->json('facet_fields')->nullable();
            $table->string('status')->default('ready');
            $table->unsignedBigInteger('document_count')->default(0);
            $table->timestamp('last_rebuilt_at')->nullable();
            $table->timestamps();
        });

        Schema::create('search_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('index_id');
            $table->string('entity_id');
            $table->string('entity_type');
            $table->text('searchable_text');
            $table->json('filterable_attributes');
            $table->json('facet_values')->nullable();
            $table->json('sortable_attributes')->nullable();
            $table->json('display_data')->nullable();
            $table->timestamps();

            $table->foreign('index_id')->references('id')->on('search_indexes')->cascadeOnDelete();
            $table->unique(['index_id', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
        });

        // Safe FULLTEXT index creation only on non-SQLite drivers (e.g. MySQL)
        $driver = Schema::connection(null)->getConnection()->getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement('ALTER TABLE search_documents ADD FULLTEXT INDEX ft_searchable_text (searchable_text)');
        }

        Schema::create('saved_searches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->string('index_name');
            $table->json('query_payload');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('search_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('index_name');
            $table->string('query_term');
            $table->json('filters_applied')->nullable();
            $table->unsignedInteger('result_count');
            $table->unsignedInteger('execution_time_ms');
            $table->string('selected_entity_id')->nullable();
            $table->unsignedInteger('selected_position')->nullable();
            $table->timestamp('searched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_analytics');
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('search_documents');
        Schema::dropIfExists('search_indexes');
    }
};
