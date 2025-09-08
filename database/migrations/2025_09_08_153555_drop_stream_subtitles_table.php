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
        Schema::dropIfExists('stream_subtitles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This will be handled by the create migration
    }
};
