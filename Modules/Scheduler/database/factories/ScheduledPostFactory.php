<?php

namespace Modules\Scheduler\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Scheduler\Models\ScheduledPost;

class ScheduledPostFactory extends Factory
{
    protected $model = ScheduledPost::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'caption' => $this->faker->sentence,
            'scheduled_at' => now()->addDay(),
            'status' => ScheduledPost::STATUS_SCHEDULED,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ScheduledPost $post) {
            $post->media()->create([
                'media_path' => 'scheduled-posts/preview.jpg',
                'position' => 0,
            ]);
        });
    }
}
