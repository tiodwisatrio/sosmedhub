<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hari libur nasional, cuti bersama, dan hari besar umum. Satu baris per tanggal;
        // hari besar bertanggal tetap diisi untuk beberapa tahun oleh seeder.
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->string('type', 20);
            $table->timestamps();

            $table->unique(['date', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
