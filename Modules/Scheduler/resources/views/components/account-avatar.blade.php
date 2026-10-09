{{--
    Foto profil akun tujuan untuk pratinjau Instagram: foto akun yang dipilih, atau huruf awal namanya
    bila akun belum punya foto. Membaca accountAvatar dan accountInitial dari x-data "postComposer".
    Ukuran, bentuk, dan bingkai diberikan lewat class.
--}}

<span {{ $attributes->class('relative inline-grid shrink-0 place-items-center overflow-hidden bg-gradient-to-tr from-primary to-primary-light font-bold text-white') }}>
    <img x-show="accountAvatar" x-cloak :src="accountAvatar" alt="" referrerpolicy="no-referrer" class="absolute inset-0 h-full w-full object-cover">
    <span x-show="! accountAvatar" x-text="accountInitial"></span>
</span>
