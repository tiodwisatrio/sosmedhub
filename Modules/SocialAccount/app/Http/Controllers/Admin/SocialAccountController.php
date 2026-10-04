<?php

namespace Modules\SocialAccount\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Modules\SocialAccount\Models\SocialAccount;
use Modules\SocialAccount\Services\InstagramGraphService;
use Throwable;

class SocialAccountController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:social-account.view', only: ['index']),
            new Middleware('permission:social-account.create', only: ['create', 'store', 'redirect', 'callback']),
            new Middleware('permission:social-account.edit', only: ['edit', 'update']),
            new Middleware('permission:social-account.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $accounts = $this->visibleAccounts()
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('social-account::admin.index', compact('accounts'));
    }

    public function create()
    {
        return view('social-account::admin.create', [
            'users' => $this->assignableUsers(),
            'account' => new SocialAccount(['platform' => SocialAccount::PLATFORM_INSTAGRAM]),
        ]);
    }

    public function redirect(InstagramGraphService $service, Request $request)
    {
        if (! $service->isConfigured()) {
            return redirect()->route('admin.social-accounts.create')
                ->with('error', 'OAuth Instagram belum dikonfigurasi. Hubungi administrator.');
        }

        $ownerId = $request->string('owner_id')->toString() ?: (string) auth()->id();
        $state = Crypt::encryptString(json_encode(['user_id' => (int) $ownerId]));

        session()->put('instagram_oauth_state', $state);

        return $service->redirect($state);
    }

    public function callback(Request $request, InstagramGraphService $service)
    {
        $state = $request->string('state')->toString();
        $expected = session()->pull('instagram_oauth_state');

        if ($state === '' || ! hash_equals((string) $expected, $state)) {
            return $this->oauthFailure('State OAuth tidak valid. Silakan coba lagi.');
        }

        if ($request->filled('error')) {
            return $this->oauthFailure('Koneksi Instagram dibatalkan atau ditolak.');
        }

        $code = $request->string('code')->toString();

        if ($code === '') {
            return $this->oauthFailure('Instagram tidak mengembalikan kode otorisasi.');
        }

        try {
            $statePayload = json_decode(Crypt::decryptString($state), true);
            $ownerId = (int) ($statePayload['user_id'] ?? auth()->id());

            $tokenData = $service->exchangeToken($code);
            $shortLivedToken = $tokenData['access_token'];
            $instagramUserId = (string) ($tokenData['user_id'] ?? '');

            $longLived = $service->exchangeToLongLived($shortLivedToken);
            $accessToken = $longLived['access_token'];
            $profile = $service->fetchProfile($accessToken);
            $instagramUserId = (string) ($profile['user_id'] ?? $instagramUserId);

            $this->persistAccount($ownerId, $accessToken, $instagramUserId, $profile, $longLived['expires_in']);

            return redirect()->route('admin.social-accounts.index')
                ->with('success', 'Akun Instagram berhasil dihubungkan.');
        } catch (Throwable $e) {
            report($e);

            return $this->oauthFailure('Gagal menghubungkan Instagram. Silakan coba lagi.');
        }
    }

    private function persistAccount(
        int $ownerId,
        string $accessToken,
        string $instagramUserId,
        array $profile,
        int $expiresIn
    ): SocialAccount {
        $username = $profile['username'] ?? $instagramUserId;
        $existing = SocialAccount::query()
            ->where('platform', SocialAccount::PLATFORM_INSTAGRAM)
            ->where('provider_account_id', $instagramUserId !== '' ? $instagramUserId : $username)
            ->first();

        $payload = [
            'user_id' => $ownerId,
            'platform' => SocialAccount::PLATFORM_INSTAGRAM,
            'provider_account_id' => $instagramUserId !== '' ? $instagramUserId : null,
            'username' => $username,
            'display_name' => $profile['username'] ?? null,
            'avatar_url' => $profile['profile_picture_url'] ?? null,
            'access_token' => $accessToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'status' => SocialAccount::STATUS_ACTIVE,
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->refresh();
        }

        return SocialAccount::create($payload);
    }

    private function oauthFailure(string $message)
    {
        return redirect()->route('admin.social-accounts.create')->with('error', $message);
    }

    public function store(Request $request)
    {
        SocialAccount::create($this->validatedData($request));

        return redirect()->route('admin.social-accounts.index')
            ->with('success', 'Akun sosial berhasil ditambahkan.');
    }

    public function edit(SocialAccount $social_account)
    {
        $this->authorizeAccountAccess($social_account);

        return view('social-account::admin.edit', [
            'account' => $social_account,
            'users' => $this->assignableUsers(),
        ]);
    }

    public function update(Request $request, SocialAccount $social_account)
    {
        $this->authorizeAccountAccess($social_account);

        $social_account->update($this->validatedData($request, $social_account));

        return redirect()->route('admin.social-accounts.index')
            ->with('success', 'Akun sosial berhasil diperbarui.');
    }

    public function destroy(SocialAccount $social_account)
    {
        $this->authorizeAccountAccess($social_account);

        $social_account->update([
            'status' => SocialAccount::STATUS_DISCONNECTED,
            'access_token' => null,
            'token_expires_at' => null,
        ]);

        return redirect()->route('admin.social-accounts.index')
            ->with('success', 'Akun sosial berhasil diputuskan.');
    }

    private function visibleAccounts(): Builder
    {
        $query = SocialAccount::query();

        if (auth()->user()?->isDeveloper()) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    private function assignableUsers()
    {
        if (! auth()->user()?->isDeveloper()) {
            return collect([auth()->user()]);
        }

        return User::query()
            ->where('approval_status', User::APPROVAL_APPROVED)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'developer'))
            ->orderBy('name')
            ->get();
    }

    private function validatedData(Request $request, ?SocialAccount $account = null): array
    {
        $isDeveloper = auth()->user()?->isDeveloper();

        $data = $request->validate([
            'user_id' => [$isDeveloper ? 'required' : 'nullable', 'integer', Rule::exists('users', 'id')],
            'platform' => ['required', Rule::in([SocialAccount::PLATFORM_INSTAGRAM])],
            'provider_account_id' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'access_token' => ['nullable', 'string'],
            'token_expires_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in([
                SocialAccount::STATUS_ACTIVE,
                SocialAccount::STATUS_DISCONNECTED,
                SocialAccount::STATUS_EXPIRED,
            ])],
        ]);

        $data['user_id'] = $isDeveloper ? $data['user_id'] : auth()->id();

        if (($data['access_token'] ?? null) === null && $account) {
            unset($data['access_token']);
        }

        return $data;
    }

    private function authorizeAccountAccess(SocialAccount $account): void
    {
        abort_unless(
            auth()->user()?->isDeveloper() || $account->user_id === auth()->id(),
            403,
            'Akun sosial ini bukan milik akun kamu.'
        );
    }
}
