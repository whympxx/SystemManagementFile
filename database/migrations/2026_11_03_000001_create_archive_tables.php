<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 20)->default('slate');
            $table->text('keywords')->nullable(); // dipisah koma, dipakai auto-kategori
            $table->boolean('is_fallback')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'slug']);
        });

        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('folders')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
            $table->string('archive_number', 40);
            $table->string('title');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20);
            $table->unsignedBigInteger('size');
            $table->text('description')->nullable();
            $table->date('document_date')->nullable();
            $table->date('expires_at')->nullable();
            $table->longText('content_text')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('analysis_status', 20)->default('pending'); // pending|done|failed
            $table->string('classified_by', 20)->nullable();            // ai|keyword|manual
            $table->text('analysis_error')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_id', 'archive_number']);
            $table->index(['user_id', 'category_id']);
            $table->index(['user_id', 'expires_at']);
        });

        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['document_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('categories');
    }
};
