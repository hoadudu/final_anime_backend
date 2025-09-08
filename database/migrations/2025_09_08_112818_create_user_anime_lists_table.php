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
        Schema::create('user_anime_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->default('My List');
            $table->enum('type', ['default', 'custom'])->default('default');
            $table->boolean('is_default')->default(true);
            $table->enum('visibility', ['public', 'private', 'friends_only'])->default('private');
            $table->timestamps();
            
            // Index for performance
            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'visibility']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_anime_lists');
    }
};
