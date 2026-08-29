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
        Schema::create('tentangkami', function (Blueprint $table) {
            $table->id();
            $table->string('tentangkami_tagline')->nullable();
            $table->string('tentangkami_judul');
            $table->text('tentangkami_deskripsi');
            $table->string('tentangkami_gambar')->nullable();
            $table->string('tentangkami_visi')->nullable();
            $table->text('tentangkami_misi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tentangkami');
    }
};
