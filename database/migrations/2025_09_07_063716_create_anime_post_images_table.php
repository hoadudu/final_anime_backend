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
        Schema::create('anime_post_images', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('post_id')->index('fk_anime_post_images_post_id');
            $table->string('image_url')->nullable();
            $table->string('alt_text')->nullable();
            $table->enum('image_type', ['poster', 'cover', 'banner', 'thumbnail', 'screenshot', 'gallery', 'other'])->default('cover');
            $table->char('language', 2)->nullable()->default('en');
            $table->boolean('is_primary')->nullable()->default(false);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_post_images');
    }
};
