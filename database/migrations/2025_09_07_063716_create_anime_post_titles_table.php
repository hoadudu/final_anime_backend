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
        Schema::create('anime_post_titles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('post_id')->index('fk_anime_post_titles_post_id');
            $table->string('title');
            $table->enum('type', ['Default', 'Synonym', 'Official', 'Alternative'])->default('Official');
            $table->char('language', 2)->nullable();
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
        Schema::dropIfExists('anime_post_titles');
    }
};
