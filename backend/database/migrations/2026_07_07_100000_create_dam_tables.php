<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hierarchical Folders
        Schema::create('dam_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->uuid('parent_id')->nullable();
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('dam_folders')->onDelete('cascade');
        });

        // 2. Physical Stored Files
        Schema::create('dam_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('storage_provider', 50)->default('local'); // local, s3, azure, r2
            $table->string('disk', 50)->default('public');
            $table->string('path', 255);
            $table->string('checksum_sha256', 64)->index();
            $table->string('checksum_md5', 32)->index();
            $table->string('mime_type', 100);
            $table->bigInteger('file_size');
            
            // Searchable Metadata columns
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // video/audio in seconds
            $table->integer('pages')->nullable(); // document page counts
            $table->integer('dpi')->nullable();
            $table->string('orientation', 30)->nullable(); // landscape, portrait, square
            
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Logical Assets
        Schema::create('dam_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id')->nullable();
            $table->uuid('current_version_id')->nullable(); // Defer FK to version model to avoid loop
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('asset_type', 30); // image, video, audio, document, archive, other
            $table->string('status', 30)->default('uploading'); // uploading, processing, ready, archived, deleted, failed
            
            $table->integer('attachment_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            
            $table->timestamp('archived_at')->nullable();
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('folder_id')->references('id')->on('dam_folders')->onDelete('set null');
        });

        // 4. Asset Versions (Linking Assets to physical files)
        Schema::create('dam_asset_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->uuid('file_id');
            $table->integer('version_number')->default(1);

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('dam_files')->onDelete('restrict');
        });

        // Add foreign key constraint to dam_assets.current_version_id now
        Schema::table('dam_assets', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('dam_asset_versions')->onDelete('set null');
        });

        // 5. Asset Conversions (Derivatives like thumbnails, webp copies)
        Schema::create('dam_asset_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->uuid('version_id');
            $table->uuid('file_id');
            $table->string('conversion_name', 50); // e.g. 'thumbnail', 'webp_optimized'
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
            $table->foreign('version_id')->references('id')->on('dam_asset_versions')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('dam_files')->onDelete('cascade');
        });

        // 6. Tags Taxonomy
        Schema::create('dam_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        // 7. Asset-Tag Pivot
        Schema::create('dam_asset_tag', function (Blueprint $table) {
            $table->uuid('asset_id');
            $table->uuid('tag_id');
            
            $table->primary(['asset_id', 'tag_id']);
            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('dam_tags')->onDelete('cascade');
        });

        // 8. Custom Collections (albums/groupings)
        Schema::create('dam_collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->text('description')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 9. Collection-Asset Pivot
        Schema::create('dam_collection_asset', function (Blueprint $table) {
            $table->uuid('collection_id');
            $table->uuid('asset_id');

            $table->primary(['collection_id', 'asset_id']);
            $table->foreign('collection_id')->references('id')->on('dam_collections')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
        });

        // 10. Polymorphic Attachments
        Schema::create('dam_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->string('attachable_type', 150);
            $table->char('attachable_id', 36);
            $table->string('attachment_role', 50); // primary, gallery, thumbnail, hero, document, creative, invoice, proof
            
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
            $table->index(['attachable_type', 'attachable_id']);
        });

        // 11. AI Metadata Isolation Table (AI-Ready OCR, captions)
        Schema::create('dam_asset_ai', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->text('caption')->nullable();
            $table->longText('ocr_text')->nullable();
            $table->json('objects')->nullable();
            $table->json('labels')->nullable();
            $table->json('dominant_colors')->nullable();
            $table->json('faces_detected')->nullable();
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('dam_assets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dam_asset_ai');
        Schema::dropIfExists('dam_attachments');
        Schema::dropIfExists('dam_collection_asset');
        Schema::dropIfExists('dam_collections');
        Schema::dropIfExists('dam_asset_tag');
        Schema::dropIfExists('dam_tags');
        Schema::dropIfExists('dam_asset_conversions');
        
        // Remove self-referential / looped FKs first
        Schema::table('dam_assets', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });

        Schema::dropIfExists('dam_asset_versions');
        Schema::dropIfExists('dam_assets');
        Schema::dropIfExists('dam_files');
        Schema::dropIfExists('dam_folders');
    }
};
