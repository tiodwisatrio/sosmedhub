<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris per format (feed, story, reel) agar status dan galat tiap format terpisah:
        // Feed bisa terbit sementara Reels gagal.
        Schema::create('scheduled_post_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->cascadeOnDelete();
            $table->string('format', 20);
            $table->string('status', 20)->default('pending');
            $table->string('ig_media_id')->nullable();
            // Kemajuan penerbitan: container Instagram dan ID hasil terbit per item.
            $table->json('state')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('share_to_feed')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['scheduled_post_id', 'format']);
            $table->index('status');
        });

        Schema::table('scheduled_post_media', function (Blueprint $table) {
            $table->string('format', 20)->default('feed')->after('scheduled_post_id');
            $table->string('type', 10)->default('image')->after('format');
            $table->unsignedInteger('duration_ms')->nullable()->after('size');
            $table->string('mime', 50)->nullable()->after('duration_ms');
        });

        // Jadwal yang sudah ada menjadi satu publikasi Feed.
        DB::table('scheduled_posts')->orderBy('id')->chunkById(200, function ($posts) {
            $rows = $posts->map(fn ($post) => [
                'scheduled_post_id' => $post->id,
                'format' => 'feed',
                'status' => match ($post->status) {
                    'published' => 'published',
                    'failed' => 'failed',
                    'publishing' => 'publishing',
                    default => 'pending',
                },
                'ig_media_id' => $post->ig_media_id,
                'error_message' => $post->error_message,
                'share_to_feed' => true,
                'published_at' => $post->published_at,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            DB::table('scheduled_post_publications')->insert($rows);
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_post_media', function (Blueprint $table) {
            $table->dropColumn(['format', 'type', 'duration_ms', 'mime']);
        });

        Schema::dropIfExists('scheduled_post_publications');
    }
};
