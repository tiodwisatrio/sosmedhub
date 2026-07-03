<?php

use App\Models\User;
use Modules\Faq\Models\Faq;
use Spatie\Permission\Models\Permission;

function faqManager(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::firstOrCreate(['name' => 'faq.view', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'faq.create', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'faq.edit', 'guard_name' => 'web']),
        Permission::firstOrCreate(['name' => 'faq.delete', 'guard_name' => 'web']),
    ]);

    return $user;
}

test('user tanpa permission tidak bisa akses halaman faq', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.faqs.index'))
        ->assertForbidden();
});

test('faq baru bisa dibuat', function () {
    $this->actingAs(faqManager())
        ->post(route('admin.faqs.store'), [
            'pertanyaan' => 'Apa itu layanan ini?',
            'jawaban' => 'Layanan pembuatan website.',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.faqs.index'));

    expect(Faq::where('pertanyaan', 'Apa itu layanan ini?')->exists())->toBeTrue();
});

test('faq gagal dibuat tanpa jawaban', function () {
    $this->actingAs(faqManager())
        ->post(route('admin.faqs.store'), [
            'pertanyaan' => 'Apa itu layanan ini?',
            'status' => 1,
        ])
        ->assertSessionHasErrors('jawaban');
});

test('faq bisa diupdate', function () {
    $faq = Faq::create(['pertanyaan' => 'Lama?', 'jawaban' => 'Jawaban lama', 'status' => 1]);

    $this->actingAs(faqManager())
        ->put(route('admin.faqs.update', $faq), [
            'pertanyaan' => 'Baru?',
            'jawaban' => 'Jawaban baru',
            'status' => 1,
        ])
        ->assertRedirect(route('admin.faqs.index'));

    expect($faq->fresh()->pertanyaan)->toBe('Baru?');
});

test('faq bisa dihapus', function () {
    $faq = Faq::create(['pertanyaan' => 'Apa itu?', 'jawaban' => 'Ini itu.', 'status' => 1]);

    $this->actingAs(faqManager())
        ->delete(route('admin.faqs.destroy', $faq))
        ->assertRedirect(route('admin.faqs.index'));

    expect(Faq::where('id', $faq->id)->exists())->toBeFalse();
});
