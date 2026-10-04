@php($contactEmail = $siteSetting->email ?? null)

@if ($contactEmail)
    <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
@else
    <span class="text-amber-300">[email kontak belum diisi di Pengaturan Situs]</span>
@endif
