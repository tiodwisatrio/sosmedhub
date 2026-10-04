<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->cascadeOnDelete();
            $table->string('media_path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['scheduled_post_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_post_media');
    }
};
