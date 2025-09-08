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
        Schema::create('anime_post_producers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('producer_id');
            $table->enum('type', ['producer', 'licensor', 'studio', 'other']);            
            $table->foreign('post_id')->references('id')->on('anime_posts')->onDelete('cascade');
            $table->foreign('producer_id')->references('id')->on('anime_producers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_post_producers');
    }
};
