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
        Schema::create('anime_episode_streams', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('episode_id')->index('fk_anime_episode_streams_episode_id');
            $table->string('server_name')->nullable(); // Tên server (VIP, HD, Backup, etc.)
            $table->string('url')->nullable(); // URL stream
            $table->json('meta')->nullable(); // Metadata bổ sung (headers, params, etc.)
            $table->enum('stream_type', ['direct', 'embed', 'hls', 'm3u8', 'dash', 'other'])->default('direct');
            $table->enum('quality', ['360p', '480p', '720p', '1080p', '4k', 'auto'])->default('auto');
            $table->enum('language', ['sub', 'dub', 'raw'])->default('sub');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            // Foreign key constraint
            $table->foreign('episode_id', 'fk_anime_episode_streams_episode_id')
                  ->references('id')->on('anime_episodes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_episode_streams');
    }
};
