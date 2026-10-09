<?php

namespace Modules\Scheduler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Modules\Scheduler\Http\Requests\Concerns\ValidatesPostFormats;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Rules\OnScheduleSlot;
use Modules\SocialAccount\Models\SocialAccount;

class UpdateScheduledPostRequest extends FormRequest
{
    use ValidatesPostFormats;

    public function authorize(): bool
    {
        return $this->user()?->can('scheduler.edit') ?? false;
    }

    protected function targetPost(): ?ScheduledPost
    {
        $post = $this->route('scheduled_post');

        return $post instanceof ScheduledPost ? $post : null;
    }

    protected function lockedFormats(): array
    {
        return $this->targetPost()
            ? $this->targetPost()->publications()->where('status', 'published')->pluck('format')->all()
            : [];
    }

    protected function keptMediaCount(string $format): int
    {
        $removed = array_map('intval', (array) $this->input('remove_media', []));

        return (int) $this->targetPost()?->media()
            ->where('format', $format)
            ->whereNotIn('id', $removed ?: [0])
            ->count();
    }

    public function rules(): array
    {
        return [
            ...$this->formatRules(),
            'social_account_id' => ['required', 'integer', $this->socialAccountRule()],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['integer'],
            'scheduled_at' => ['required', 'date', $this->futureWibRule(), new OnScheduleSlot],
        ];
    }

    /**
     * Antarmuka menerima waktu dalam WIB, jadi perbandingan
     * dengan "now" harus dilakukan dalam zona waktu yang sama.
     */
    private function futureWibRule(): \Closure
    {
        return function (string $attribute, mixed $value, $fail): void {
            if (Carbon::parse($value, ScheduledPost::WIB)->isPast()) {
                $fail('Tanggal terbit harus di masa depan.');
            }
        };
    }

    private function socialAccountRule(): Exists
    {
        return Rule::exists('social_accounts', 'id')
            ->whereIn('platform', [SocialAccount::PLATFORM_INSTAGRAM, SocialAccount::PLATFORM_FACEBOOK])
            ->where('status', SocialAccount::STATUS_ACTIVE)
            ->when(! $this->user()?->isDeveloper(), fn ($rule) => $rule->where('user_id', $this->user()?->id));
    }
}
