<?php

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Modules\Menu\Models\Menu;
use Modules\Scheduler\Models\ScheduledPost;
use Spatie\Permission\Models\Permission;

function localeAdminUser(): User
{
    $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED]);
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'scheduler.view', 'guard_name' => 'web']));

    return $user;
}

it('memakai Bahasa Indonesia secara bawaan', function () {
    $this->get('/')->assertOk()->assertSee('<html lang="id"', false);
});

it('mengganti bahasa lewat route locale.update dan menyimpannya di cookie', function () {
    $this->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect()
        ->assertCookie('locale', 'en');
});

it('menolak kode bahasa yang tidak didukung', function () {
    $this->post(route('locale.update'), ['locale' => 'xx'])
        ->assertSessionHasErrors('locale')
        ->assertCookieMissing('locale');
});

it('memakai pilihan bahasa dari cookie pada permintaan berikutnya', function () {
    $this->withCookie('locale', 'en')
        ->get(route('login'))
        ->assertOk()
        ->assertSee('<html lang="en"', false);
});

it('mengabaikan nilai cookie yang tidak valid dan kembali ke bawaan', function () {
    $this->withCookie('locale', 'zz')
        ->get(route('login'))
        ->assertSee('<html lang="id"', false);
});

it('mengganti ikon notifikasi di topbar admin dengan pilihan bahasa', function () {
    $this->actingAs(localeAdminUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pilih bahasa')
        ->assertSee('Bahasa Indonesia')
        ->assertSee('English')
        ->assertSee(route('locale.update'), false)
        // path ikon lonceng lama tidak boleh ada lagi
        ->assertDontSee('M14.857 17.082a23.848', false);
});

it('menerjemahkan menu sidebar dan menu pengguna ke bahasa yang dipilih', function () {
    Menu::create(['label' => 'Penjadwalan', 'route_name' => 'admin.scheduled-posts.index', 'permission' => 'scheduler.view', 'urutan' => 1, 'is_active' => 1]);

    $user = localeAdminUser();

    $this->actingAs($user)->get(route('admin.dashboard'))
        ->assertSee('Penjadwalan')
        ->assertSee('Profil Saya')
        ->assertSee('Keluar');

    $this->actingAs($user)->withCookie('locale', 'en')->get(route('admin.dashboard'))
        ->assertSee('Scheduling')
        ->assertSee('My Profile')
        ->assertSee('Log out')
        ->assertDontSee('Profil Saya');
});

it('memformat tanggal jadwal mengikuti bahasa aktif', function () {
    $post = new ScheduledPost(['scheduled_at' => '2026-10-21 03:00:00']);

    app()->setLocale('id');
    expect($post->formattedScheduledAt())->toContain('Okt');

    app()->setLocale('en');
    expect($post->formattedScheduledAt())->toContain('Oct');
});

/**
 * Setiap teks literal di __('...') harus punya padanan di lang/en.json supaya tidak ada teks
 * Indonesia yang tertinggal saat bahasa Inggris dipilih.
 */
it('memiliki terjemahan Inggris untuk semua teks yang dibungkus __()', function () {
    // Teks sumber Indonesia diterjemahkan lewat en.json; teks sumber Inggris (bawaan Breeze) lewat id.json.
    $translations = [
        ...json_decode(file_get_contents(base_path('lang/en.json')), true, flags: JSON_THROW_ON_ERROR),
        ...json_decode(file_get_contents(base_path('lang/id.json')), true, flags: JSON_THROW_ON_ERROR),
    ];

    $files = [];
    foreach (['app', 'resources/views', 'Modules'] as $dir) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($dir), FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            $path = $file->getPathname();

            if (str_contains($path, '/tests/') || str_contains($path, '/vendor/') || str_contains($path, '/node_modules/')) {
                continue;
            }

            if (str_ends_with($path, '.php')) {
                $files[] = $path;
            }
        }
    }

    $missing = [];

    foreach ($files as $path) {
        $code = file_get_contents($path);

        preg_match_all('/(?<![A-Za-z0-9_>:$])__\(\s*\'((?:[^\'\\\\]|\\\\.)*)\'/', $code, $single);
        preg_match_all('/(?<![A-Za-z0-9_>:$])__\(\s*"((?:[^"\\\\]|\\\\.)*)"/', $code, $double);

        foreach ([...$single[1], ...$double[1]] as $key) {
            $key = stripslashes($key);

            // Kunci framework seperti "auth.password" diterjemahkan oleh file bahasa Laravel, bukan JSON.
            if (preg_match('/^[a-z_]+(\.[a-z_]+)+$/', $key)) {
                continue;
            }

            if (! array_key_exists($key, $translations)) {
                $missing[$key] = str_replace(base_path().'/', '', $path);
            }
        }
    }

    expect($missing)->toBe([], 'Teks belum ada di lang/en.json atau lang/id.json: '.json_encode($missing, JSON_UNESCAPED_UNICODE));
});

it('menerjemahkan semua label menu bawaan seeder', function () {
    $translations = json_decode(file_get_contents(base_path('lang/en.json')), true, flags: JSON_THROW_ON_ERROR);

    preg_match_all("/'label'\s*=>\s*'([^']+)'/", file_get_contents(base_path('Modules/Menu/database/seeders/MenuDatabaseSeeder.php')), $m);

    foreach ($m[1] as $label) {
        expect($translations)->toHaveKey($label);
    }
});

it('menyediakan saklar bahasa di halaman login', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Pilih bahasa')
        ->assertSee(route('locale.update'), false);
});

it('menampilkan halaman login dalam bahasa yang dipilih', function () {
    $this->get(route('login'))->assertSee('Ingat saya')->assertSee('Lupa kata sandi?');

    $this->withCookie('locale', 'en')->get(route('login'))
        ->assertSee('Remember me')
        ->assertDontSee('Ingat saya');
});

/**
 * Halaman isi yang sudah diterjemahkan tidak boleh memuat teks Indonesia saat bahasa Inggris dipilih.
 * Daftar frasa ini sengaja mencakup teks yang paling mudah terlewat: atribut, tombol, dan keterangan.
 */
it('tidak menampilkan teks Indonesia di halaman isi saat bahasa Inggris dipilih', function () {
    $user = User::factory()->create(['approval_status' => User::APPROVAL_APPROVED]);
    $user->givePermissionTo(array_map(
        fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
        ['scheduler.view', 'scheduler.create', 'scheduler.edit', 'scheduler.delete', 'social-account.view', 'social-account.create', 'social-account.delete']
    ));

    $indonesian = [
        'Buat Postingan', 'Total Postingan', 'Status Postingan', 'Hari Penting', 'Kembali ke hari ini',
        'Jadwalkan Terbit', 'Ringkasan jadwal', 'Pratinjau layar penuh', 'Akun Tujuan', 'Waktu Indonesia Barat',
        'Pilih satu atau lebih', 'Tulis caption', 'Tanggal & Jam Terbit', 'Belum ada caption',
        'Belum ada riwayat postingan', 'Cari caption', 'Semua akun',
        'Hubungkan akun media sosial', 'Belum ada akun terhubung', 'Segera hadir', '+ Tambah Akun',
        'Selamat datang', 'Anda berhasil masuk',
    ];

    $pages = [
        'dashboard' => route('admin.dashboard'),
        'penjadwalan' => route('admin.scheduled-posts.index'),
        'buat jadwal' => route('admin.scheduled-posts.create'),
        'riwayat' => route('admin.post-history.index'),
        'akun sosial' => route('admin.social-accounts.index'),
    ];

    foreach ($pages as $name => $url) {
        $html = $this->actingAs($user)->withCookie('locale', 'en')->get($url)->assertOk()->getContent();

        foreach ($indonesian as $phrase) {
            expect($html)->not->toContain($phrase, "Halaman {$name} masih memuat teks Indonesia: {$phrase}");
        }
    }

    // Dan bahasa Inggrisnya benar-benar tampil.
    $this->actingAs($user)->withCookie('locale', 'en')->get(route('admin.scheduled-posts.create'))
        ->assertSee('Schedule to Publish')
        ->assertSee('Schedule summary')
        ->assertSee('Full-screen preview');
});

it('menampilkan bendera bahasa aktif di tombol dan kedua bendera di dropdown', function () {
    $user = localeAdminUser();

    $html = $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->getContent();

    // Tombol (bendera bahasa aktif) lalu dua baris dropdown: Indonesia dan English.
    expect(substr_count($html, 'data-flag="id"'))->toBe(2)
        ->and(substr_count($html, 'data-flag="en"'))->toBe(1);

    $html = $this->actingAs($user)->withCookie('locale', 'en')->get(route('admin.dashboard'))->assertOk()->getContent();

    expect(substr_count($html, 'data-flag="en"'))->toBe(2)
        ->and(substr_count($html, 'data-flag="id"'))->toBe(1)
        // bendera dekoratif: disembunyikan dari pembaca layar, nama bahasa tetap tertulis
        ->and($html)->toContain('aria-hidden="true"');
});

it('tidak menampilkan apa pun untuk kode bahasa tanpa bendera', function () {
    expect(trim(Blade::render('<x-flag locale="xx" />')))->toBe('');
});
