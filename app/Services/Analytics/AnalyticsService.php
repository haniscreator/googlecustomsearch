<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;
use Illuminate\Support\Carbon;

class AnalyticsService
{
    /**
     * Basic summary for the admin dashboard.
     */
    public function getSummary(): array
    {
        $totalSearches = AnalyticsEvent::where('event_type', 'search_performed')->count();

        $uniqueQueries = AnalyticsEvent::where('event_type', 'search_performed')
            ->whereNotNull('query')
            ->distinct('query')
            ->count('query');

        $lastSearch = AnalyticsEvent::where('event_type', 'search_performed')
            ->latest('created_at')
            ->first();

        return [
            'total_searches' => $totalSearches,
            'unique_queries' => $uniqueQueries,
            'last_search_at' => $lastSearch?->created_at?->toIso8601String(),
        ];
    }

    /**
     * Last N searches for simple listing.
     */
    public function getRecentSearches(int $limit = 10): array
    {
        return AnalyticsEvent::where('event_type', 'search_performed')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get([
                'id',
                'query',
                'results_count',
                'provider',
                'created_at',
            ])
            ->toArray();
    }
}
