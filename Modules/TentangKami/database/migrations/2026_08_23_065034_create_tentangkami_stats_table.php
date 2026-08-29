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
        Schema::create('tentangkami_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tentangkami_id');
            $table->foreign('tentangkami_id')->references('id')->on('tentangkami')->onDelete('cascade');
            $table->string('tentangkami_stats_gambar')->nullable();
            $table->string('tentangkami_stats_label');
            $table->integer('tentangkami_stats_angka');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tentangkami_stats');
    }
};
