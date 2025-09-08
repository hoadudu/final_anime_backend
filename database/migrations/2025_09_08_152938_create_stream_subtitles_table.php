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
        Schema::create('stream_subtitles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('stream_id')->comment('Reference to anime_episode_streams.id');
            $table->string('language', 10)->comment('Language code: vi, en, ja, etc.');
            $table->string('language_name', 50)->comment('Display name: Vietnamese, English, etc.');
            $table->enum('type', ['srt', 'vtt', 'ass', 'ssa', 'txt'])->default('srt');
            $table->text('url')->comment('URL to subtitle file (CDN, S3, external)');
            $table->enum('source', ['manual', 'auto', 'community', 'official'])->default('manual');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable()->comment('Metadata: encoding, offset, fps');
            $table->timestamps();

            // Indexes
            $table->index(['stream_id', 'language'], 'idx_stream_language');
            $table->index(['stream_id', 'is_default'], 'idx_stream_default');
            $table->unique(['stream_id', 'language', 'type'], 'unique_stream_lang_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_subtitles');
    }
};
