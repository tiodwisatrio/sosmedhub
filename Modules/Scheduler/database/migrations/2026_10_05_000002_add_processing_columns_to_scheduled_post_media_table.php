<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_post_media', function (Blueprint $table) {
            // Kosong setelah versi terbit dihapus; thumbnail tetap disimpan.
            $table->string('media_path')->nullable()->change();
            $table->string('thumbnail_path')->nullable()->after('media_path');
            $table->unsignedInteger('width')->nullable()->after('thumbnail_path');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->unsignedInteger('size')->nullable()->after('height');
            $table->timestamp('media_pruned_at')->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_post_media', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_path', 'width', 'height', 'size', 'media_pruned_at']);
        });
    }
};
