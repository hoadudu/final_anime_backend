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
        Schema::create('anime_post_characters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('character_id');
            $table->enum('role', ['main', 'supporting', 'minor','other']);            
            $table->foreign('post_id')->references('id')->on('anime_posts')->onDelete('cascade');
            $table->foreign('character_id')->references('id')->on('anime_characters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_post_characters');
    }
};
