<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('instagram')->index();
            $table->string('provider_account_id')->nullable();
            $table->string('username');
            $table->string('display_name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'provider_account_id']);
            $table->index(['user_id', 'platform', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
