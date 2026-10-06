<?php

namespace Modules\SocialAccount\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Crypt;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Services\FacebookGraphService;
use Throwable;

class FacebookConnectController extends Controller implements HasMiddleware
{
    private const STATE_KEY = 'facebook_oauth_state';

    private const PENDING_KEY = 'facebook_pending_connection';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:social-account.create'),
        ];
    }

    public function redirect(FacebookGraphService $service, Request $request)
    {
        if (! $service->isConfigured()) {
            return $this->failure('OAuth Facebook belum dikonfigurasi. Hubungi administrator.');
        }

        // Hanya developer yang boleh memilih pemilik akun; client selalu untuk dirinya sendiri.
        $ownerId = auth()->user()?->isDeveloper() && $request->filled('owner_id')
            ? (int) $request->input('owner_id')
            : (int) auth()->id();

        $state = Crypt::encryptString(json_encode(['user_id' => $ownerId]));

        session()->put(self::STATE_KEY, $state);

        return $service->redirect($state);
    }

    public function callback(Request $request, FacebookGraphService $service)
    {
        $state = $request->string('state')->toString();
        $expected = session()->pull(self::STATE_KEY);

        if ($state === '' || ! hash_equals((string) $expected, $state)) {
            return $this->failure('State OAuth tidak valid. Silakan coba lagi.');
        }

        if ($request->filled('error')) {
            return $this->failure('Koneksi Facebook dibatalkan atau ditolak.');
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            return $this->failure('Facebook tidak mengembalikan kode otorisasi.');
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true);
            $ownerId = (int) ($payload['user_id'] ?? auth()->id());

            $userToken = $service->exchangeToLongLived($service->exchangeCode($code));

            $missing = array_diff($service->scopes(), $service->grantedPermissions($userToken));

            if ($missing !== []) {
                return $this->failure('Izin Facebook belum lengkap. Setujui semua izin yang diminta lalu coba lagi.');
            }

            $pages = $service->pages($userToken);
        } catch (Throwable $e) {
            report($e);

            return $this->failure('Gagal menghubungkan Facebook. Silakan coba lagi.');
        }

        if ($pages === []) {
            return $this->failure('Tidak ada Halaman Facebook yang bisa kamu kelola. Pastikan kamu admin Halaman dan memilih Halaman itu saat memberi izin.');
        }

        if (count($pages) === 1) {
            $this->persistPages($ownerId, $pages);

            return redirect()->route('admin.social-accounts.index')
                ->with('success', 'Halaman Facebook berhasil dihubungkan.');
        }

        // Token pengguna disimpan terenkripsi di sesi hanya sampai Halaman dipilih.
        session()->put(self::PENDING_KEY, Crypt::encryptString(json_encode([
            'user_id' => $ownerId,
            'token' => $userToken,
        ])));

        return redirect()->route('admin.social-accounts.facebook.pages');
    }

    public function pages(FacebookGraphService $service)
    {
        $pending = $this->pending();

        if (! $pending) {
            return $this->failure('Sesi pemilihan Halaman berakhir. Silakan hubungkan lagi.');
        }

        try {
            $pages = $service->pages($pending['token']);
        } catch (Throwable $e) {
            report($e);
            session()->forget(self::PENDING_KEY);

            return $this->failure('Gagal mengambil daftar Halaman Facebook. Silakan coba lagi.');
        }

        $connected = SocialAccount::query()
            ->where('platform', SocialAccount::PLATFORM_FACEBOOK)
            ->where('status', SocialAccount::STATUS_ACTIVE)
            ->whereIn('provider_account_id', array_column($pages, 'id'))
            ->pluck('provider_account_id')
            ->all();

        return view('social-account::admin.facebook-pages', [
            'pages' => $pages,
            'connected' => $connected,
        ]);
    }

    public function connect(Request $request, FacebookGraphService $service)
    {
        $pending = $this->pending();

        if (! $pending) {
            return $this->failure('Sesi pemilihan Halaman berakhir. Silakan hubungkan lagi.');
        }

        $data = $request->validate([
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['string'],
        ], [
            'pages.required' => 'Pilih minimal satu Halaman.',
            'pages.min' => 'Pilih minimal satu Halaman.',
        ]);

        try {
            // Token tidak pernah dikirim dari browser: daftar Halaman diambil ulang dari Facebook
            // sehingga hanya Halaman milik pengguna yang bisa dipilih.
            $chosen = array_values(array_filter(
                $service->pages($pending['token']),
                fn (array $page) => in_array($page['id'], $data['pages'], true)
            ));
        } catch (Throwable $e) {
            report($e);

            return $this->failure('Gagal menghubungkan Facebook. Silakan coba lagi.');
        }

        if ($chosen === []) {
            return back()->with('error', 'Halaman yang dipilih tidak ditemukan.');
        }

        $this->persistPages($pending['user_id'], $chosen);
        session()->forget(self::PENDING_KEY);

        return redirect()->route('admin.social-accounts.index')
            ->with('success', count($chosen).' Halaman Facebook berhasil dihubungkan.');
    }

    private function persistPages(int $ownerId, array $pages): void
    {
        foreach ($pages as $page) {
            SocialAccount::updateOrCreate(
                [
                    'platform' => SocialAccount::PLATFORM_FACEBOOK,
                    'provider_account_id' => $page['id'],
                ],
                [
                    'user_id' => $ownerId,
                    'username' => $page['username'] ?: $page['name'],
                    'display_name' => $page['name'],
                    'avatar_url' => $page['avatar_url'],
                    'access_token' => $page['access_token'],
                    // Token Halaman dari token pengguna long-lived tidak kedaluwarsa.
                    'token_expires_at' => null,
                    'status' => SocialAccount::STATUS_ACTIVE,
                ]
            );
        }
    }

    private function pending(): ?array
    {
        $encrypted = session()->get(self::PENDING_KEY);

        if (! $encrypted) {
            return null;
        }

        try {
            $pending = json_decode(Crypt::decryptString($encrypted), true);
        } catch (DecryptException) {
            return null;
        }

        return isset($pending['token'], $pending['user_id']) ? $pending : null;
    }

    private function failure(string $message)
    {
        return redirect()->route('admin.social-accounts.index')->with('error', $message);
    }
}
