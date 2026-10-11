<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bahasa pilihan pengguna; dipakai email notifikasi (dikirim tanpa cookie) dan lintas perangkat.
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 8)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
