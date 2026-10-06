<?php

namespace Modules\Scheduler\Http\Requests\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Rules\FormatMedia;

/**
 * Validasi format (Feed, Story, Reels) dan media tiap format, dipakai form buat dan ubah.
 *
 * Kolom unggahan: media[] (Feed), media_story[], media_reel[].
 * Tanpa pilihan format, dianggap Feed saja agar form lama tetap berjalan.
 */
trait ValidatesPostFormats
{
    private const LIMITS = [
        ScheduledPost::FORMAT_FEED => ['min' => 1, 'max' => 10, 'field' => 'media', 'noun' => 'foto'],
        ScheduledPost::FORMAT_STORY => ['min' => 1, 'max' => 10, 'field' => 'media_story', 'noun' => 'foto atau video'],
        ScheduledPost::FORMAT_REEL => ['min' => 1, 'max' => 1, 'field' => 'media_reel', 'noun' => 'video'],
    ];

    /**
     * Format yang dipilih, berurutan, termasuk format yang sudah terbit (terkunci) saat mengubah.
     *
     * @return list<string>
     */
    public function selectedFormats(): array
    {
        $input = $this->input('formats');
        $input = is_array($input) && $input !== [] ? array_map('strval', $input) : [ScheduledPost::FORMAT_FEED];
        $selected = array_values(array_intersect(ScheduledPost::FORMATS, $input));

        return array_values(array_unique([...$selected, ...$this->lockedFormats()]));
    }

    /**
     * Format yang sudah terbit tidak bisa diubah; kolom unggahannya diabaikan.
     *
     * @return list<string>
     */
    protected function lockedFormats(): array
    {
        return [];
    }

    /**
     * Jumlah media format ini yang masih akan tersimpan (khusus ubah; buat selalu 0).
     */
    protected function keptMediaCount(string $format): int
    {
        return 0;
    }

    /**
     * @return array<string, list<UploadedFile>>
     */
    public function mediaByFormat(): array
    {
        $result = [];

        foreach ($this->editableFormats() as $format) {
            $files = $this->file(self::LIMITS[$format]['field']);
            $result[$format] = is_array($files) ? array_values($files) : [];
        }

        return $result;
    }

    /**
     * Urutan akhir media per format dari form: "e:ID" untuk media yang sudah tersimpan dan
     * "n:N" untuk file unggahan ke-N (urutan unggah). Hanya format yang bisa diubah.
     *
     * @return array<string, list<string>>
     */
    public function mediaOrder(): array
    {
        $order = [];

        foreach ($this->editableFormats() as $format) {
            $tokens = $this->input("order.{$format}");

            if (is_array($tokens)) {
                $order[$format] = array_values(array_filter($tokens, fn ($t) => is_string($t) && preg_match('/^[en]:\d+$/', $t)));
            }
        }

        return $order;
    }

    /**
     * @return list<string>
     */
    protected function editableFormats(): array
    {
        return array_values(array_diff($this->selectedFormats(), $this->lockedFormats()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatRules(): array
    {
        $editable = $this->editableFormats();
        $rules = [
            'formats' => ['nullable', 'array'],
            'formats.*' => [Rule::in(ScheduledPost::FORMATS)],
            'share_to_feed' => ['nullable', 'boolean'],
            'order' => ['nullable', 'array'],
            'order.*' => ['nullable', 'array', 'max:10'],
            'order.*.*' => ['string', 'regex:/^[en]:\d+$/'],
            // Caption dipakai Feed dan Reels; Story tidak mendukung caption.
            'caption' => [in_array(ScheduledPost::FORMAT_FEED, $this->selectedFormats(), true) ? 'required' : 'nullable', 'string', 'max:2200'],
        ];

        foreach (self::LIMITS as $format => $limit) {
            $field = $limit['field'];
            $rules[$field] = ['nullable', 'array', 'max:'.$limit['max']];
            $rules["{$field}.*"] = in_array($format, $editable, true)
                ? ['bail', 'file', new FormatMedia($format)]
                : ['prohibited'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->editableFormats() as $format) {
                $limit = self::LIMITS[$format];
                $count = $this->keptMediaCount($format) + count($this->file($limit['field']) ?? []);
                $label = ScheduledPost::formatLabel($format);

                if ($count < $limit['min']) {
                    $validator->errors()->add($limit['field'], "{$label} membutuhkan minimal {$limit['min']} {$limit['noun']}.");
                } elseif ($count > $limit['max']) {
                    $validator->errors()->add($limit['field'], $format === ScheduledPost::FORMAT_REEL
                        ? 'Reels hanya bisa berisi satu video.'
                        : "{$label} maksimal {$limit['max']} {$limit['noun']}.");
                }
            }
        });
    }
}
