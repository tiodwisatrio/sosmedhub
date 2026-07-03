@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $siteName = $siteSetting->app_name ?? config('app.name');
    $metaTitle = $title ? $title.' — '.$siteName : $siteName;
    $metaDescription = $description ?: $siteSetting->deskripsi;
    $metaDescription = $metaDescription ? Str::limit(strip_tags($metaDescription), 160) : null;
    $metaImage = $image ?: ($siteSetting->og_image ? Storage::url($siteSetting->og_image) : null);
    $canonicalUrl = url()->current();
@endphp

<title>{{ $metaTitle }}</title>
@if ($metaDescription)
    <meta name="description" content="{{ $metaDescription }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if ($metaDescription)
    <meta property="og:description" content="{{ $metaDescription }}">
@endif
@if ($metaImage)
    <meta property="og:image" content="{{ $metaImage }}">
@endif

<meta name="twitter:card" content="{{ $metaImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
@if ($metaDescription)
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endif
@if ($metaImage)
    <meta name="twitter:image" content="{{ $metaImage }}">
@endif
