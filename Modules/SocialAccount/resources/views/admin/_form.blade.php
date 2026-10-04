@php
    $selectedUserId = old('user_id', $account->user_id ?: auth()->id());
@endphp

@if (auth()->user()?->isDeveloper())
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Owner</label>
        <x-admin.select
            name="user_id"
            :options="$users->pluck('name', 'id')->toArray()"
            :selected="$selectedUserId"
            placeholder="Pilih owner"
        />
    </div>
@endif

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Platform</label>
    <x-admin.select
        name="platform"
        :options="[\Modules\SocialAccount\Models\SocialAccount::PLATFORM_INSTAGRAM => 'Instagram']"
        :selected="old('platform', $account->platform ?: \Modules\SocialAccount\Models\SocialAccount::PLATFORM_INSTAGRAM)"
    />
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
    <x-admin.input-text name="username" :value="old('username', $account->username)" placeholder="brand_kamu" />
    @error('username') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Display Name</label>
    <x-admin.input-text name="display_name" :value="old('display_name', $account->display_name)" placeholder="Brand Kamu" />
    @error('display_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Instagram Account ID</label>
    <x-admin.input-text name="provider_account_id" :value="old('provider_account_id', $account->provider_account_id)" placeholder="Diisi otomatis oleh OAuth nanti" />
    @error('provider_account_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Avatar URL</label>
    <x-admin.input-text name="avatar_url" type="url" :value="old('avatar_url', $account->avatar_url)" placeholder="https://..." />
    @error('avatar_url') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Access Token</label>
    <x-admin.textarea name="access_token" rows="3" placeholder="{{ $account->exists ? 'Kosongkan jika tidak ingin mengganti token' : 'Token dari OAuth nanti' }}"></x-admin.textarea>
    @error('access_token') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Token Expired At</label>
    <x-admin.input-text
        name="token_expires_at"
        type="datetime-local"
        :value="old('token_expires_at', $account->token_expires_at?->format('Y-m-d\TH:i'))"
    />
    @error('token_expires_at') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
    <x-admin.select
        name="status"
        :options="[
            \Modules\SocialAccount\Models\SocialAccount::STATUS_ACTIVE => 'Active',
            \Modules\SocialAccount\Models\SocialAccount::STATUS_DISCONNECTED => 'Disconnected',
            \Modules\SocialAccount\Models\SocialAccount::STATUS_EXPIRED => 'Expired',
        ]"
        :selected="old('status', $account->status ?: \Modules\SocialAccount\Models\SocialAccount::STATUS_ACTIVE)"
    />
</div>
