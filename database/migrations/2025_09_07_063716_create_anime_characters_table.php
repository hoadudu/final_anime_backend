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
        Schema::create('anime_characters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mal_id');
            $table->json('images')->nullable();
            $table->string('name');
            $table->string('name_kanji')->nullable();
            $table->json('nicknames')->nullable();
            $table->text('about')->nullable();
            $table->string('slug')->unique();            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_characters');
    }
};
