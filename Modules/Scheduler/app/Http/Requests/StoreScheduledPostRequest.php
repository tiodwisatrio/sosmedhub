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

class StoreScheduledPostRequest extends FormRequest
{
    use ValidatesPostFormats;

    public function authorize(): bool
    {
        return $this->user()?->can('scheduler.create') ?? false;
    }

    public function rules(): array
    {
        return [
            ...$this->formatRules(),
            'social_account_id' => ['required', 'integer', $this->socialAccountRule()],
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
        // Publikasi Facebook belum tersedia, jadi hanya akun Instagram yang bisa dijadwalkan.
        return Rule::exists('social_accounts', 'id')
            ->where('platform', SocialAccount::PLATFORM_INSTAGRAM)
            ->where('status', SocialAccount::STATUS_ACTIVE)
            ->when(! $this->user()?->isDeveloper(), fn ($rule) => $rule->where('user_id', $this->user()?->id));
    }
}
