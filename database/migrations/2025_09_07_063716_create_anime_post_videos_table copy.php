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
        Schema::create('anime_post_videos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('post_id')->index('fk_anime_post_videos_post_id');
            $table->string('title')->nullable();
            $table->string('url')->nullable();
            $table->json('meta')->nullable();
            $table->enum('video_type', ['promo', 'music_videos', 'episodes', 'other'])->default('other');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_post_videos');
    }
};
