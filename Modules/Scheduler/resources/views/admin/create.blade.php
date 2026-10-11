@extends('layouts.admin')

@section('title', __('Jadwalkan Postingan'))

@section('header')
    <h1 class="text-lg font-semibold text-slate-800">{{ __('Jadwalkan Postingan') }}</h1>
@endsection

@section('content')
    <x-scheduler::composer :social-accounts="$socialAccounts" />
@endsection
