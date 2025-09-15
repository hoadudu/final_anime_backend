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
        Schema::create('anime_translation_teams', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 200);
            $table->string('home', 200)->nullable();
            $table->string('logo', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_translation_teams');
    }
};
