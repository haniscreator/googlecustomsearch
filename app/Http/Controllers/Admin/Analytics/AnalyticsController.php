<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Simple JSON summary for admin dashboard.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->analyticsService->getSummary();
        $recent  = $this->analyticsService->getRecentSearches(10);

        return response()->json([
            'summary'        => $summary,
            'recent_searches'=> $recent,
        ]);
    }
}
