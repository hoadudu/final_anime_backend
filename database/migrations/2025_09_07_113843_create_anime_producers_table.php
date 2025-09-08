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
        Schema::create('anime_producers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mal_id');
            $table->string('slug')->unique();
            $table->json('titles');
            $table->json('images');
            $table->string('established')->nullable();
            $table->text('about')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_producers');
    }
};
