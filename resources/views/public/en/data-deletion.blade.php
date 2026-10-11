@extends('layouts.public')

@section('title', 'Data Deletion')
@section('description', 'How to disconnect your Instagram account and Facebook Pages and request deletion of your data from Sosmedhub.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Last updated October 11, 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Data Deletion</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                You can stop Sosmedhub's access to your Instagram account and Facebook Pages, and ask for all of your data to be deleted. There are three ways, from the fastest.
            </p>

            <div class="legal mt-4">
                <h2>1. Revoke access on Instagram or Facebook</h2>
                <p>This stops Sosmedhub's access immediately, without waiting for us.</p>
                <p><strong>Instagram</strong></p>
                <ol>
                    <li>Open Instagram, then <strong>Settings</strong>.</li>
                    <li>Choose <strong>Apps and websites</strong>.</li>
                    <li>Find Sosmedhub and choose <strong>Remove</strong>.</li>
                </ol>
                <p><strong>Facebook</strong></p>
                <ol>
                    <li>Open Facebook, then <strong>Settings &amp; privacy</strong> &rarr; <strong>Settings</strong>.</li>
                    <li>Choose <strong>Apps and websites</strong>. If Sosmedhub is not listed there, check <strong>Business Integrations</strong>.</li>
                    <li>Find Sosmedhub and choose <strong>Remove</strong>.</li>
                </ol>
                <p>Once access is revoked, Sosmedhub can no longer publish to your account or Page.</p>

                <h2>2. Disconnect the account from Sosmedhub</h2>
                <ol>
                    <li>Sign in to Sosmedhub and open <strong>Social Accounts</strong>.</li>
                    <li>Choose the Instagram account or Facebook Page you want to disconnect and remove the connection.</li>
                </ol>
                <p>The account's access token is deleted from our system immediately, and scheduled posts for that account will not be published.</p>

                <h2>3. Request deletion of all your data</h2>
                <p>Send a request to @include('public._contact') from the email address you used to register, with the subject <strong>Delete Sosmedhub Data</strong>. Include the Instagram username or Facebook Page name you connected, if any.</p>
                <p>We will verify that the request comes from the account owner, then delete:</p>
                <ul>
                    <li>your Sosmedhub account (name, email, phone number);</li>
                    <li>the data and access tokens of the connected Instagram accounts and Facebook Pages;</li>
                    <li>all scheduled posts, captions, and the photos and videos you uploaded.</li>
                </ul>
                <p>We aim to complete the request within 30 days at the most and will notify you by email once it is done.</p>

                <h2>What is not deleted</h2>
                <p>Posts that have already been published on Instagram or your Facebook Page remain on your account and are managed by you through that platform. Sosmedhub does not delete them.</p>

                <p class="mt-10 border-t border-white/10 pt-6">How we collect and use data is described in the <a href="{{ route('privacy') }}">privacy policy</a>.</p>
            </div>
        </div>
    </article>
@endsection
