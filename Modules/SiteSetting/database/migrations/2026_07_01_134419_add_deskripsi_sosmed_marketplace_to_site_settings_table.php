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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('app_name');
            $table->string('og_image')->nullable()->after('icon');
            $table->text('iframe_map')->nullable()->after('og_image');

            $table->string('instagram_nama')->nullable()->after('iframe_map');
            $table->string('instagram_link')->nullable()->after('instagram_nama');
            $table->string('facebook_nama')->nullable()->after('instagram_link');
            $table->string('facebook_link')->nullable()->after('facebook_nama');
            $table->string('tiktok_nama')->nullable()->after('facebook_link');
            $table->string('tiktok_link')->nullable()->after('tiktok_nama');
            $table->string('youtube_nama')->nullable()->after('tiktok_link');
            $table->string('youtube_link')->nullable()->after('youtube_nama');
            $table->string('x_nama')->nullable()->after('youtube_link');
            $table->string('x_link')->nullable()->after('x_nama');

            $table->string('shopee_nama')->nullable()->after('x_link');
            $table->string('shopee_link')->nullable()->after('shopee_nama');
            $table->string('tokopedia_nama')->nullable()->after('shopee_link');
            $table->string('tokopedia_link')->nullable()->after('tokopedia_nama');
            $table->string('blibli_nama')->nullable()->after('tokopedia_link');
            $table->string('blibli_link')->nullable()->after('blibli_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'deskripsi',
                'og_image',
                'iframe_map',
                'instagram_nama',
                'instagram_link',
                'facebook_nama',
                'facebook_link',
                'tiktok_nama',
                'tiktok_link',
                'youtube_nama',
                'youtube_link',
                'x_nama',
                'x_link',
                'shopee_nama',
                'shopee_link',
                'tokopedia_nama',
                'tokopedia_link',
                'blibli_nama',
                'blibli_link',
            ]);
        });
    }
};
