<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

/**
 * Menerjemahkan pesan yang sudah tersimpan di database (misalnya error_message hasil penerbitan)
 * saat ditampilkan. Pesan disimpan dalam bahasa sumber (Indonesia) sebagai teks final, jadi
 * dicocokkan kembali dengan kunci terjemahan, termasuk kunci berplaceholder ":nama".
 */
class StoredMessage
{
    /** @var array<string, array<string, array{regex: string, names: list<string>}>> */
    private static array $templates = [];

    public static function translate(?string $text): ?string
    {
        if ($text === null || $text === '' || app()->getLocale() === config('sosmedhub.default_locale', 'id')) {
            return $text;
        }

        // Beberapa format digabung: "Feed: pesan | Story: pesan".
        return implode(' | ', array_map(
            fn (string $part) => self::translatePart(trim($part)),
            explode(' | ', $text)
        ));
    }

    private static function translatePart(string $part): string
    {
        $prefix = '';

        if (preg_match('/^(Feed|Story|Reels): (.+)$/su', $part, $m)) {
            [$prefix, $part] = [$m[1].': ', $m[2]];
        }

        if (Lang::has($part)) {
            return $prefix.__($part);
        }

        foreach (self::templates() as $key => $template) {
            if (preg_match($template['regex'], $part, $match)) {
                $params = [];

                foreach ($template['names'] as $name) {
                    $params[$name] = $match[$name];
                }

                return $prefix.__($key, $params);
            }
        }

        return $prefix.$part;
    }

    /**
     * Kunci terjemahan berplaceholder diubah jadi ekspresi reguler; yang terpanjang dicoba lebih dulu
     * supaya templat yang lebih spesifik menang.
     *
     * @return array<string, array{regex: string, names: list<string>}>
     */
    private static function templates(): array
    {
        $locale = app()->getLocale();

        if (isset(self::$templates[$locale])) {
            return self::$templates[$locale];
        }

        $keys = array_keys(Lang::getLoader()->load($locale, '*', '*'));
        usort($keys, fn ($a, $b) => strlen($b) <=> strlen($a));

        $templates = [];

        foreach ($keys as $key) {
            if (! preg_match_all('/:([a-z_]+)/', $key, $names) || $names[1] === []) {
                continue;
            }

            // Pecah kunci di sekitar placeholder agar bagian tetap di-quote dan placeholder jadi grup.
            $seen = [];
            $regex = '';

            foreach (preg_split('/(:[a-z_]+)/', $key, -1, PREG_SPLIT_DELIM_CAPTURE) as $piece) {
                if (preg_match('/^:([a-z_]+)$/', $piece, $p)) {
                    $name = $p[1];
                    $regex .= isset($seen[$name]) ? '(?P='.$name.')' : '(?<'.$name.'>.+?)';
                    $seen[$name] = true;
                } else {
                    $regex .= preg_quote($piece, '/');
                }
            }

            $templates[$key] = ['regex' => '/^'.$regex.'$/su', 'names' => array_keys($seen)];
        }

        return self::$templates[$locale] = $templates;
    }
}
