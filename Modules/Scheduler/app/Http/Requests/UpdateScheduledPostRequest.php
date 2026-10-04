<?php

namespace Modules\Scheduler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Rules\InstagramPhoto;
use Modules\Scheduler\Rules\OnScheduleSlot;
use Modules\SocialAccount\Models\SocialAccount;

class UpdateScheduledPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scheduler.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'caption' => ['required', 'string', 'max:2200'],
            'social_account_id' => ['required', 'integer', $this->socialAccountRule()],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['bail', 'image', 'mimes:jpg,jpeg', 'max:8192', new InstagramPhoto],
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
            ->where('status', SocialAccount::STATUS_ACTIVE)
            ->when(! $this->user()?->isDeveloper(), fn ($rule) => $rule->where('user_id', $this->user()?->id));
    }

    public function messages(): array
    {
        return [
            'media.*.image' => 'File harus berupa foto.',
            'media.*.mimes' => 'Foto harus berformat JPEG (.jpg atau .jpeg). Instagram tidak menerima format lain lewat API.',
            'media.*.max' => 'Ukuran tiap foto maksimal 8 MB.',
        ];
    }
}
