<?php

namespace Modules\Scheduler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\SocialAccount\Models\SocialAccount;

class StoreScheduledPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scheduler.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'caption' => ['required', 'string', 'max:2200'],
            'social_account_id' => ['required', 'integer', $this->socialAccountRule()],
            'media' => ['required', 'array', 'min:1', 'max:10'],
            'media.*' => ['image', 'mimes:jpeg,png', 'max:8192'],
            'scheduled_at' => ['required', 'date', $this->futureWibRule()],
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
}
