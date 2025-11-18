<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Render the admin analytics page (Inertia).
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Analytics/Overview');
    }

    /**
     * JSON summary for the admin analytics page.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->analyticsService->getSummary();
        $recent  = $this->analyticsService->getRecentSearches(10);

        return response()->json([
            'summary'         => $summary,
            'recent_searches' => $recent,
        ]);
    }
}
