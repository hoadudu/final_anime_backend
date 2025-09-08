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
        Schema::create('user_anime_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('user_anime_lists')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('anime_posts')->onDelete('cascade');
            $table->enum('status', ['watching', 'completed', 'on_hold', 'dropped', 'plan_to_watch'])->default('plan_to_watch');
            $table->tinyInteger('score')->nullable()->comment('Score from 1-10');
            $table->text('note')->nullable();
            $table->timestamps();
            
            // Unique constraint - one anime per list
            $table->unique(['list_id', 'post_id']);
            
            // Indexes for performance
            $table->index(['list_id', 'status']);
            $table->index(['post_id']);
            $table->index(['list_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_anime_list_items');
    }
};
