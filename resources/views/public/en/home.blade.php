@extends('layouts.public')

@section('title', 'Instagram and Facebook Content Scheduling')
@section('description', 'Sosmedhub helps business owners schedule photos, videos, and captions for Instagram and Facebook Pages, then publishes them automatically at the chosen time.')

@section('content')
    <section class="px-6 pb-24 pt-36 md:pt-44">
        <div class="mx-auto grid max-w-5xl items-center gap-14 md:grid-cols-[1.15fr_1fr]">
            <div>
                <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Instagram and Facebook content scheduling</p>
                <h1 class="mt-5 text-5xl leading-[1.05] md:text-7xl" style="font-family: 'Instrument Serif', serif;">
                    Plan today.<br>Publish on schedule.
                </h1>
                <p class="mt-6 max-w-md leading-relaxed text-white/60">
                    Upload a photo or video, write a caption, and choose a publish time. Sosmedhub publishes it to your business's Instagram account and Facebook Page automatically, without you opening the app when the time comes.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-3">
                    @auth
                        @if (auth()->user()->isDeveloper() || auth()->user()->isApproved())
                            <a href="{{ route('admin.dashboard') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Open dashboard</a>
                        @else
                            <a href="{{ route('approval.pending') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">View approval status</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full px-5 py-3 text-sm text-white/60 transition hover:text-white">Create an account</a>
                    @endauth
                </div>
                @guest
                    @unless (config('sosmedhub.registration.auto_approve'))
                        <p class="mt-4 text-xs text-white/35">New accounts are approved by an administrator first.</p>
                    @endunless
                @endguest
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5" aria-label="Example of the schedule view">
                <p class="mb-4 text-[10px] uppercase tracking-[0.28em] text-white/35">Example view</p>
                <ul class="divide-y divide-white/10">
                    <li class="flex items-center justify-between gap-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-white/90">Weekend promo, 20% off the whole menu</p>
                            <p class="mt-0.5 text-xs text-white/40">Sat, 9:00 AM UTC+7</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-400/10 px-2.5 py-1 text-xs text-amber-300">Scheduled</span>
                    </li>
                    <li class="flex items-center justify-between gap-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-white/90">New on the menu: palm sugar iced coffee</p>
                            <p class="mt-0.5 text-xs text-white/40">Fri, 5:30 PM UTC+7</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-400/10 px-2.5 py-1 text-xs text-amber-300">Scheduled</span>
                    </li>
                    <li class="flex items-center justify-between gap-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-white/90">Thanks for stopping by this week</p>
                            <p class="mt-0.5 text-xs text-white/40">Thu, 12:00 PM UTC+7</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs text-emerald-300">Published</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-5xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">How it works</p>
            <h2 class="mt-4 max-w-lg text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Three steps, then let it run.</h2>

            <ol class="mt-14 grid gap-10 md:grid-cols-3">
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Step 1</p>
                    <h3 class="mt-2 text-lg font-medium">Connect your account</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Sign in with your Instagram Business or Creator account, or choose a Facebook Page you manage, on each platform's official page. We never see your password.
                    </p>
                </li>
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Step 2</p>
                    <h3 class="mt-2 text-lg font-medium">Build your schedule</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Choose the destination account and a Feed, Story, or Reels format, upload a photo or video, write a caption, and pick a date and time. One schedule can publish to several formats at once.
                    </p>
                </li>
                <li>
                    <p class="text-sm text-white/35" style="font-family: 'Instrument Serif', serif;">Step 3</p>
                    <h3 class="mt-2 text-lg font-medium">Publish automatically</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        When the time comes, the post goes live on your account. If a format fails, you get an email with the reason, and only that format is published again when you reschedule.
                    </p>
                </li>
            </ol>
        </div>
    </section>

    @php
        $reelIg = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel'));
        $reelFb = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('reel', 'facebook'));
        $feedVideo = \Modules\Scheduler\Services\VideoSpec::duration(\Modules\Scheduler\Services\VideoSpec::maxSeconds('feed'));
    @endphp

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-5xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Supported formats</p>
            <h2 class="mt-4 max-w-xl text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">One schedule, to the platforms you choose.</h2>

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">
                    <p class="text-lg font-medium">Instagram</p>
                    <p class="mt-1 text-xs text-white/40">Business or Creator accounts</p>
                    <dl class="mt-6 space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Feed</dt>
                            <dd class="text-right text-white/55">Photo or video; mixed carousel of up to 10 items (video up to {{ $feedVideo }})</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Story</dt>
                            <dd class="text-right text-white/55">Photo or video up to 60 seconds</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Reels</dt>
                            <dd class="text-right text-white/55">Video up to {{ $reelIg }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">
                    <p class="text-lg font-medium">Facebook</p>
                    <p class="mt-1 text-xs text-white/40">Pages you manage</p>
                    <dl class="mt-6 space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Feed</dt>
                            <dd class="text-right text-white/55">One photo or an album of up to 10 photos</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Story</dt>
                            <dd class="text-right text-white/55">Photo or video up to 60 seconds</dd>
                        </div>
                        <div class="flex items-start justify-between gap-6">
                            <dt class="text-white/80">Reels</dt>
                            <dd class="text-right text-white/55">Video up to {{ $reelFb }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-5xl">
            <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Your data</p>
            <h2 class="mt-4 max-w-xl text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">You stay in control.</h2>

            <ul class="mt-12 grid gap-8 md:grid-cols-3">
                <li>
                    <h3 class="text-lg font-medium">No passwords</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">You sign in on Instagram's and Facebook's official pages. We never receive your social media password.</p>
                </li>
                <li>
                    <h3 class="text-lg font-medium">Only what you create</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Sosmedhub only publishes posts you schedule yourself. We do not read your messages, comments, or followers.</p>
                </li>
                <li>
                    <h3 class="text-lg font-medium">Disconnect and delete any time</h3>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Access tokens are stored encrypted and deleted when you disconnect an account. You can request deletion of all your data at any time.</p>
                </li>
            </ul>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto grid max-w-5xl gap-12 md:grid-cols-[1fr_1.2fr]">
            <div>
                <p class="text-[10px] uppercase tracking-[0.32em] text-white/40">Instagram and Facebook permissions</p>
                <h2 class="mt-4 text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Only what is needed to publish.</h2>
            </div>

            <div class="space-y-8">
                <p class="text-[10px] uppercase tracking-[0.28em] text-white/35">Instagram</p>
                <div>
                    <p class="font-mono text-sm text-white">instagram_business_basic</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Reads the username, account type, and profile picture so the right account appears in your list.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">instagram_business_content_publish</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Publishes the photos, videos, and captions you schedule to your account (Feed, Stories, and Reels). Only for posts you create yourself in Sosmedhub.</p>
                </div>

                <p class="border-t border-white/10 pt-6 text-[10px] uppercase tracking-[0.28em] text-white/35">Facebook (Pages)</p>
                <div>
                    <p class="font-mono text-sm text-white">pages_show_list</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Shows the list of Pages you manage so you can choose one to connect.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">pages_read_engagement</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Reads basic Page data (name and picture) and the Page token needed to publish, as well as video processing status.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">pages_manage_posts</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Publishes the photos, videos, and captions you schedule to your Page (Feed, Stories, and Reels). Only for posts you create yourself in Sosmedhub.</p>
                </div>
                <div>
                    <p class="font-mono text-sm text-white">business_management</p>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">Shows Pages owned by your Business Portfolio, which do not appear without this permission.</p>
                </div>
                <p class="border-t border-white/10 pt-6 text-sm leading-relaxed text-white/55">
                    You can disconnect an account at any time from the Social Accounts menu or from your Instagram and Facebook settings.
                    Read more on the <a href="{{ route('data-deletion') }}" class="text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">data deletion page</a>
                    and in the <a href="{{ route('privacy') }}" class="text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">privacy policy</a>.
                </p>
            </div>
        </div>
    </section>

    <section class="border-t border-white/10 px-6 py-24">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-4xl md:text-5xl" style="font-family: 'Instrument Serif', serif;">Ready to schedule your first post?</h2>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                @auth
                    @if (auth()->user()->isDeveloper() || auth()->user()->isApproved())
                        <a href="{{ route('admin.dashboard') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Open dashboard</a>
                    @else
                        <a href="{{ route('approval.pending') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">View approval status</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="liquid-glass rounded-full px-7 py-3 text-sm font-medium text-white">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-full px-5 py-3 text-sm text-white/60 transition hover:text-white">Create an account</a>
                @endauth
            </div>
            <p class="mt-8 text-sm text-white/45">Questions? Contact @include('public._contact').</p>
        </div>
    </section>
@endsection
