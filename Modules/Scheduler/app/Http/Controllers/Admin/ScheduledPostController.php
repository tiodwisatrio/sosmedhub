<?php

namespace Modules\Scheduler\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Scheduler\Http\Requests\StoreScheduledPostRequest;
use Modules\Scheduler\Http\Requests\UpdateScheduledPostRequest;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\Scheduler\Services\ScheduledPostService;
use Modules\SocialAccount\Models\SocialAccount;

class ScheduledPostController extends Controller implements HasMiddleware
{
    public function __construct(private ScheduledPostService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:scheduler.view', only: ['index']),
            new Middleware('permission:scheduler.create', only: ['create', 'store']),
            new Middleware('permission:scheduler.edit', only: ['edit', 'update', 'cancel']),
            new Middleware('permission:scheduler.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $wib = ScheduledPost::WIB;
        $todayWib = Carbon::now($wib);
        $weekParam = (string) request('week');
        $hasValidWeek = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekParam);
        $blockStart = $hasValidWeek
            ? Carbon::parse($weekParam, $wib)->startOfWeek(Carbon::MONDAY)
            : $todayWib->copy()->startOfWeek(Carbon::MONDAY);
        $blockEnd = $blockStart->copy()->addWeeks(4);

        $posts = $this->visibleScheduledPosts()
            ->with(['media', 'socialAccount'])
            ->where('status', ScheduledPost::STATUS_SCHEDULED)
            ->whereBetween('scheduled_at', [
                $blockStart->copy()->utc(),
                $blockEnd->copy()->utc(),
            ])
            ->orderBy('scheduled_at')
            ->get();

        $postsByDay = $posts->groupBy(
            fn (ScheduledPost $post) => $post->scheduled_at->setTimezone($wib)->toDateString()
        );

        $postsForModal = $posts->map(function (ScheduledPost $post) {
            $media = $post->media
                ->filter(fn ($item) => Storage::disk('public')->exists($item->media_path))
                ->values();

            return [
                'id' => $post->id,
                'caption' => $post->caption,
                'media' => $media->map(fn ($item) => Storage::url($item->media_path))->values(),
                'media_url' => $media->first() ? Storage::url($media->first()->media_path) : null,
                'scheduled_at' => $post->formattedScheduledAt().' WIB',
                'status' => $post->statusLabel(),
                'created_at' => $post->created_at?->format('d M Y'),
            ];
        })->values();

        $weeks = collect(range(0, 3))
            ->map(function (int $weekIndex) use ($blockStart, $todayWib, $postsByDay) {
                $weekStart = $blockStart->copy()->addWeeks($weekIndex);

                return collect(range(0, 6))
                    ->map(function (int $dayIndex) use ($weekStart, $todayWib, $postsByDay) {
                        $date = $weekStart->copy()->addDays($dayIndex);

                        return [
                            'date' => $date,
                            'posts' => $postsByDay->get($date->toDateString(), collect()),
                            'isToday' => $date->isSameDay($todayWib),
                        ];
                    });
            });

        $historyPosts = $this->visibleScheduledPosts()
            ->with('socialAccount')
            ->where(function ($query) {
                $query
                    ->where('status', '!=', ScheduledPost::STATUS_SCHEDULED)
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->latest('scheduled_at')
            ->paginate(5);

        return view('scheduler::admin.index', [
            'weeks' => $weeks,
            'blockStart' => $blockStart,
            'previousBlock' => $blockStart->copy()->subWeeks(4),
            'nextBlock' => $blockEnd,
            'currentBlock' => $todayWib->copy()->startOfWeek(Carbon::MONDAY),
            'postsCount' => $posts->count(),
            'postsForModal' => $postsForModal,
            'historyPosts' => $historyPosts,
        ]);
    }

    public function create()
    {
        return view('scheduler::admin.create', [
            'socialAccounts' => $this->availableSocialAccounts(),
        ]);
    }

    public function store(StoreScheduledPostRequest $request)
    {
        $data = $request->safe()->only(['caption', 'scheduled_at', 'social_account_id']);

        $this->service->store($data, $request->file('media') ?? [], auth()->id());

        return redirect()->route('admin.scheduled-posts.index')
            ->with('success', 'Postingan berhasil dijadwalkan.');
    }

    public function edit(ScheduledPost $scheduled_post)
    {
        $this->authorizePostAccess($scheduled_post);
        abort_unless($scheduled_post->canBeEdited(), 403, 'Postingan yang sudah lewat tidak bisa diubah lagi.');

        return view('scheduler::admin.edit', [
            'post' => $scheduled_post->load(['media', 'socialAccount']),
            'socialAccounts' => $this->availableSocialAccounts($scheduled_post),
        ]);
    }

    public function update(UpdateScheduledPostRequest $request, ScheduledPost $scheduled_post)
    {
        $this->authorizePostAccess($scheduled_post);
        abort_unless($scheduled_post->canBeEdited(), 403, 'Postingan yang sudah lewat tidak bisa diubah lagi.');

        $data = $request->safe()->only(['caption', 'scheduled_at', 'social_account_id']);

        $this->service->update(
            $scheduled_post,
            $data,
            $request->file('media') ?? [],
            $request->input('remove_media') ?? []
        );

        return redirect()->route('admin.scheduled-posts.index')
            ->with('success', 'Postingan berhasil diperbarui.');
    }

    public function cancel(ScheduledPost $scheduled_post)
    {
        $this->authorizePostAccess($scheduled_post);
        abort_unless($scheduled_post->canBeEdited(), 403, 'Postingan yang sudah lewat tidak bisa dibatalkan lagi.');

        $this->service->cancel($scheduled_post);

        return redirect()->route('admin.scheduled-posts.index')
            ->with('success', 'Postingan berhasil dibatalkan.');
    }

    public function destroy(ScheduledPost $scheduled_post)
    {
        $this->authorizePostAccess($scheduled_post);

        if (! $scheduled_post->isPublished()) {
            $this->service->deleteMedia($scheduled_post);
        }

        $scheduled_post->delete();

        return redirect()->route('admin.scheduled-posts.index')
            ->with('success', 'Postingan berhasil dihapus.');
    }

    private function visibleScheduledPosts(): Builder
    {
        return $this->scopeForCurrentUser(ScheduledPost::query());
    }

    private function scopeForCurrentUser(Builder $query): Builder
    {
        if (auth()->user()?->isDeveloper()) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    private function authorizePostAccess(ScheduledPost $post): void
    {
        abort_unless(
            auth()->user()?->isDeveloper() || $post->user_id === auth()->id(),
            403,
            'Postingan ini bukan milik akun kamu.'
        );
    }

    private function availableSocialAccounts(?ScheduledPost $post = null)
    {
        $query = SocialAccount::query()->where('status', SocialAccount::STATUS_ACTIVE);

        if (! auth()->user()?->isDeveloper()) {
            $query->where('user_id', auth()->id());
        }

        $accounts = $query->orderBy('username')->get();

        if ($post?->socialAccount && ! $accounts->contains('id', $post->socialAccount->id)) {
            $accounts->push($post->socialAccount);
        }

        return $accounts;
    }
}
