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
        Schema::create('anime_genres', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mal_id');
            $table->string('name');         
            $table->string('slug')->unique();
            $table->string('description', 500)->nullable();
            $table->enum('type', ['genres', 'explicit_genres', 'themes', 'demographics'])->default('genres');           
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_genres');
    }
};
