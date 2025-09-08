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
        Schema::create('anime_post_morphables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('morphable_id');
            $table->string('morphable_type');
            $table->timestamps();
            
            // Add foreign key constraints
            $table->foreign('post_id')->references('id')->on('anime_posts')->onDelete('cascade');
            
            // Add index for better performance
            $table->index(['post_id', 'morphable_type']);
            $table->index(['morphable_id', 'morphable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_post_morphables');
    }
};
