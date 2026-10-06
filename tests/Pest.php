<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

require_once __DIR__.'/Helpers/mp4.php';

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    // Disk public selalu palsu di test, supaya unggahan tidak menulis ke storage/app/public sungguhan.
    ->beforeEach(fn () => Storage::fake('public'))
    ->in('Feature', '../Modules/*/tests/Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Membaca konfigurasi awal komponen Alpine "postComposer" dari HTML halaman buat/ubah jadwal.
 */
function composerConfig(string $html): array
{
    preg_match("/postComposer\\(JSON\\.parse\\('(.*?)'\\)\\)/s", $html, $match);

    return json_decode(str_replace('\\u0022', '"', $match[1] ?? '{}'), true) ?? [];
}
