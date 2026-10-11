@extends('layouts.public')

@section('title', 'Terms of Service')
@section('description', 'The rules for using Sosmedhub with Instagram and Facebook Pages: accounts, content, automatic publishing, and service limits.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Last updated October 11, 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Terms of Service</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                By registering for and using Sosmedhub, you agree to the following terms. If you do not agree, please do not use this service.
            </p>

            <div class="legal mt-4">
                <h2>About the service</h2>
                <p>Sosmedhub helps you schedule photos, videos, and captions, and then publishes them to your Instagram account or Facebook Page at the time you choose. Sosmedhub is not a product of Meta, Instagram, or Facebook and is not affiliated with any of them.</p>

                <h2>Your account</h2>
                <ul>
                    <li>@unless (config('sosmedhub.registration.auto_approve'))New account registrations must be approved by an administrator.@endunless We may reject, suspend, or close accounts that violate these terms.</li>
                    <li>You are responsible for keeping your password confidential and for all activity on your account.</li>
                    <li>The Instagram account you connect must be a Business or Creator account, and must belong to you or be one you are authorized to manage.</li>
                    <li>The supported Facebook destination is a Page, not a personal profile. The Page you connect must be one you manage or are authorized to manage.</li>
                </ul>

                <h2>Your content</h2>
                <ul>
                    <li>Your photos, videos, and captions remain yours. You give us permission to process them only to store your schedule and to publish them to Instagram or Facebook at your request.</li>
                    <li>You are responsible for what you publish. Content must comply with applicable law and with the terms and community guidelines of Instagram and Facebook.</li>
                    <li>You warrant that you have the rights to the photos, videos, and text you upload.</li>
                </ul>

                <h2>Automatic publishing</h2>
                <ul>
                    <li>Posts are published after the time you choose, usually within a minute. We do not guarantee timing or that publishing will succeed, because it depends on the Instagram and Facebook services.</li>
                    <li>Publishing can fail, for example because an account connection has expired, the media does not meet the platform's requirements, or Instagram or Facebook rejects the request. You will receive an email with the reason and can reschedule.</li>
                    <li>Photos must be JPEG. Videos must be MP4 or MOV (H.264 or HEVC with AAC audio). Video length limits: Stories up to 60 seconds; Reels up to {{ \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel')) }} on Instagram and {{ \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel', 'facebook')) }} on Facebook; videos inside an Instagram Feed carousel up to {{ \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('feed')) }}.</li>
                    <li>An Instagram Feed post may mix photos and videos, while a Facebook Feed post is photos only. Instagram and Facebook limit the number of posts per account in 24 hours, and that limit also applies to Sosmedhub.</li>
                    <li>Stories do not support captions, stickers, or links through automatic publishing. Any text you want to appear in a Story must already be in the image or video.</li>
                    <li>One schedule can be published to several formats (Feed, Story, Reels). Each format is processed separately, so one format may fail while another is published.</li>
                    <li>An Instagram account connection is time-limited and renewed automatically. A Facebook Page connection can end if permission is revoked or Meta cancels the token. If a connection cannot be renewed or ends, you need to reconnect the account for your schedule to keep running.</li>
                </ul>

                <h2>What you must not do</h2>
                <ul>
                    <li>Publish content that is unlawful, misleading, or that infringes the rights of others.</li>
                    <li>Connect an Instagram account or Facebook Page that belongs to someone else without permission.</li>
                    <li>Disrupt or overload the service, or try to access other users' data.</li>
                </ul>

                <h2>Ending the service</h2>
                <p>You can stop at any time by disconnecting your Instagram account or Facebook Page and requesting deletion of your data through the <a href="{{ route('data-deletion') }}">data deletion page</a>. We may stop the service or your account if these terms are violated.</p>

                <h2>Limitation of liability</h2>
                <p>The service is provided as is. To the extent permitted by law, we are not liable for indirect losses, including posts that are late, not published, or that look different on Instagram or Facebook due to causes beyond our control.</p>

                <h2>Changes to these terms</h2>
                <p>These terms may change. The latest version is shown on this page with a new update date. Using the service after a change means you accept it.</p>

                <h2>Governing law</h2>
                <p>These terms are governed by the laws of the Republic of Indonesia.</p>

                <h2>Contact</h2>
                <p>Questions about these terms can be sent to @include('public._contact'). How we handle your data is described in the <a href="{{ route('privacy') }}">privacy policy</a>.</p>
            </div>
        </div>
    </article>
@endsection
