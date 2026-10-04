<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Query "post jatuh tempo" berjalan di setiap putaran cron.
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'scheduled_at']);
        });
    }
};
