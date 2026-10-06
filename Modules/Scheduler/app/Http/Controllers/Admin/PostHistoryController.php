<?php

namespace Modules\Scheduler\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Scheduler\Models\ScheduledPost;
use Modules\SocialAccount\Models\SocialAccount;

class PostHistoryController extends Controller implements HasMiddleware
{
    private const STATUSES = [
        ScheduledPost::STATUS_PUBLISHED,
        ScheduledPost::STATUS_FAILED,
        ScheduledPost::STATUS_PARTIAL,
        ScheduledPost::STATUS_CANCELLED,
        ScheduledPost::STATUS_DRAFT,
    ];

    public static function middleware(): array
    {
        return [new Middleware('permission:scheduler.view')];
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null,
            'account' => $request->query('account') ? (int) $request->query('account') : null,
            'q' => trim((string) $request->query('q')) ?: null,
        ];

        // Jumlah di tab mengikuti filter akun dan pencarian, tetapi bukan filter status.
        $base = $this->history()
            ->when($filters['account'], fn (Builder $q, $id) => $q->where('social_account_id', $id))
            ->when($filters['q'], fn (Builder $q, $term) => $q->whereRaw("caption like ? escape '!'", ['%'.$this->escapeLike($term).'%']));

        $counts = (clone $base)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = collect(self::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->put('', (int) $counts->sum());

        $historyPosts = (clone $base)
            ->when($filters['status'], fn (Builder $q, $status) => $q->where('status', $status))
            ->with(['socialAccount', 'media', 'publications'])
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        return view('scheduler::admin.history', [
            'historyPosts' => $historyPosts,
            'counts' => $counts,
            'filters' => $filters,
            'accounts' => $this->accounts(),
        ]);
    }

    /**
     * Postingan yang sudah lewat antrean: bukan lagi menunggu giliran.
     */
    private function history(): Builder
    {
        $query = ScheduledPost::query()->where(function (Builder $query) {
            $query
                ->where('status', '!=', ScheduledPost::STATUS_SCHEDULED)
                ->orWhere('scheduled_at', '<=', now());
        });

        if (! auth()->user()?->isDeveloper()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    private function accounts()
    {
        $query = SocialAccount::query();

        if (! auth()->user()?->isDeveloper()) {
            $query->where('user_id', auth()->id());
        }

        return $query->orderBy('username')->get();
    }

    private function escapeLike(string $value): string
    {
        // Karakter escape eksplisit (!) agar perilakunya sama di MySQL dan SQLite.
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
