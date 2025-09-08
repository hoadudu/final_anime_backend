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
        Schema::table('anime_post_videos', function (Blueprint $table) {
            $table->foreign(['post_id'], 'fk_anime_post_videos_post_id')->references(['id'])->on('anime_posts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anime_post_videos', function (Blueprint $table) {
            $table->dropForeign('fk_anime_post_videos_post_id');
        });
    }
};
