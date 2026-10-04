<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('scheduled_posts')->whereNotNull('media_path')->get(['id', 'media_path']);

        foreach ($rows as $row) {
            DB::table('scheduled_post_media')->insert([
                'scheduled_post_id' => $row->id,
                'media_path' => $row->media_path,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->dropColumn('media_path');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->string('media_path')->nullable()->after('caption');
        });

        $rows = DB::table('scheduled_post_media')
            ->orderBy('scheduled_post_id')
            ->orderBy('position')
            ->get(['scheduled_post_id', 'media_path']);

        foreach ($rows as $row) {
            DB::table('scheduled_posts')
                ->whereNull('media_path')
                ->where('id', $row->scheduled_post_id)
                ->update(['media_path' => $row->media_path]);
        }
    }
};
