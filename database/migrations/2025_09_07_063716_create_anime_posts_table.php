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
        Schema::create('anime_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mal_id')->nullable()->unique('mal_id');
            $table->string('slug')->unique();
            $table->string('type')->nullable();
            $table->string('source')->nullable();
            $table->integer('episodes')->nullable();
            $table->string('status')->nullable();
            $table->boolean('airing')->default(false);
            $table->date('aired_from')->nullable();
            $table->date('aired_to')->nullable();
            $table->string('duration')->nullable();
            $table->string('rating')->nullable();
            $table->text('synopsis')->nullable();
            $table->text('background')->nullable();
            $table->string('season')->nullable();
            $table->timestamp('broadcast')->nullable();
            $table->json('external')->nullable();
            $table->boolean('approved')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_posts');
    }
};
