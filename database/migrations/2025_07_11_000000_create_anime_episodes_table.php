<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anime_episodes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('post_id');
        $table->json('titles')->nullable();
        $table->integer('episode_number')->nullable(); // số tập trong season
        $table->integer('absolute_number')->nullable(); // số tập toàn bộ (tính từ season 1 đến hiện tại)
        $table->string('thumbnail')->nullable();
        $table->date('release_date')->nullable();            
        $table->text('description')->nullable();
        $table->enum('type', ['regular', 'filler', 'recap', 'special'])->default('regular');
        $table->integer('group')->default(1); // nhóm tập (ví dụ: 1-12, 13-24, ...)
        $table->integer('sort_number')->nullable(); // số thứ tự để sắp xếp hiển thị
        $table->softDeletes();
        $table->timestamps();
        $table->foreign('post_id')->references('id')->on('anime_posts')->onDelete('cascade');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_episodes');
    }
};
