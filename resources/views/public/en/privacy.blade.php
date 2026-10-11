@extends('layouts.public')

@section('title', 'Privacy Policy')
@section('description', 'What data Sosmedhub collects from your Instagram account and Facebook Pages, what it is used for, and how you can delete it.')

@section('content')
    <article class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto max-w-3xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Last updated October 11, 2026</p>
            <h1 class="mt-4 text-5xl md:text-6xl" style="font-family: 'Instrument Serif', serif;">Privacy Policy</h1>
            <p class="mt-6 leading-relaxed text-white/60">
                Sosmedhub is a service for scheduling and publishing posts to Instagram accounts and Facebook Pages. This page explains the data we collect when you use it, what that data is used for, and your rights over it.
            </p>

            <div class="legal mt-4">
                <h2>Data we collect</h2>
                <p><strong>Sosmedhub account data.</strong> When you register: your name, email address, password (stored as a hash, never as plain text), and phone number if you provide one.</p>
                <p><strong>Instagram account data.</strong> When you connect an account: the account ID, username, display name, profile picture, account type, and the access token issued by Instagram. We never receive or store your Instagram password, because you sign in directly on Instagram's own page.</p>
                <p><strong>Facebook Page data.</strong> When you connect a Page: the Page ID, Page name, Page picture, and the Page access token issued by Facebook. We only support Pages, not personal profiles. Your Facebook user access token is used only briefly, kept encrypted in your session until you choose a Page, and then not stored. We never receive or store your Facebook password, because you sign in directly on Facebook's own page.</p>
                <p><strong>Content you schedule.</strong> Photos, videos, captions, publish time, publishing status, the ID of the post on Instagram or Facebook once published, and the error message if publishing fails. When uploaded, photos are resized to the size the platform uses and their metadata is removed, including GPS location and camera information; we do not keep the original photo file. Videos (for Feed, Stories, and Reels) are stored as uploaded, without modification. For Instagram, the platform retrieves the media from our storage address at publishing time; for Facebook, the file is sent directly from our server to Facebook.</p>
                <p><strong>Technical data.</strong> A session cookie to keep you signed in, and server error logs that may contain the time of an event and an error message. We do not use analytics or advertising trackers.</p>

                <h2>What the data is used for</h2>
                <ul>
                    <li>Running the service: showing your accounts, storing your schedule, and publishing posts at the time you choose.</li>
                    <li>Sending service-related emails, such as notifications that a post failed or was published, and that an Instagram account or Facebook Page connection needs to be reconnected.</li>
                    <li>Keeping accounts secure and preventing abuse.</li>
                </ul>
                <p>We do not sell your data and we do not use it for advertising.</p>

                <h2>Permissions we request</h2>
                <p><strong>Instagram</strong></p>
                <ul>
                    <li><code>instagram_business_basic</code>: read the username, account type, and profile picture.</li>
                    <li><code>instagram_business_content_publish</code>: publish the photos, videos, and captions you schedule to your account (Feed, Stories, and Reels).</li>
                </ul>
                <p><strong>Facebook</strong></p>
                <ul>
                    <li><code>pages_show_list</code>: show the list of Pages you manage so you can choose one.</li>
                    <li><code>pages_read_engagement</code>: read basic Page data (name, picture) and the Page token needed to publish, as well as the processing status of uploaded videos.</li>
                    <li><code>pages_manage_posts</code>: publish the photos, videos, and captions you schedule to your Page (Feed, Stories, and Reels).</li>
                    <li><code>business_management</code>: show Pages owned by your Business Portfolio, which do not appear without this permission.</li>
                </ul>
                <p>We only publish posts that you create yourself in Sosmedhub. We do not read your direct messages, comments, followers, or advertising data.</p>

                <h2>Who the data is shared with</h2>
                <ul>
                    <li><strong>Meta Platforms (Instagram and Facebook).</strong> Photos, videos, and captions are sent to Instagram or Facebook to be published, and access tokens are used to call their services. Processing on Meta's side is subject to Meta's policies.</li>
                    <li><strong>Email service provider.</strong> Your name and email address are passed on so that service emails can be delivered.</li>
                    <li><strong>Hosting provider.</strong> Data is stored on the servers of the hosting provider we use.</li>
                </ul>
                <p>Other than that, data is disclosed only when required by applicable law.</p>

                <h2>Security</h2>
                <p>Instagram access tokens and Facebook Page tokens are stored encrypted and are never shown in the interface or in error logs. Access to the application requires signing in.@unless (config('sosmedhub.registration.auto_approve')) New accounts must be approved by an administrator before they can be used.@endunless No system is completely free of risk, but we make reasonable efforts to protect your data.</p>

                <h2>How long data is kept</h2>
                <p>Data is kept for as long as your account is active. Photos and videos uploaded for publishing are deleted {{ config('scheduler.media.publish_retention_days', 30) }} days after the post is published; only a small version of each photo is kept for the history. When you disconnect an Instagram account or Facebook Page, its access token is deleted immediately and scheduled posts for that account will not be published. You can ask for all of your data to be deleted at any time, as described on the <a href="{{ route('data-deletion') }}">data deletion page</a>.</p>

                <h2>Your rights</h2>
                <p>You have the right to know what data we hold, to request corrections, and to request deletion of your personal data, in accordance with the personal data protection regulations applicable in Indonesia. You can also revoke Sosmedhub's permissions at any time through your Instagram or Facebook settings.</p>

                <h2>Changes to this policy</h2>
                <p>If this policy changes, the latest version is shown on this page with a new update date.</p>

                <h2>Contact</h2>
                <p>Questions about privacy can be sent to @include('public._contact').</p>
            </div>
        </div>
    </article>
@endsection
