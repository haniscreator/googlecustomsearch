<?php

namespace App\Actions\Search;

use App\Models\SearchHistory;

class StoreHistoryAction
{
    /**
     * Store a record of a search operation.
     *
     * @param  string  $query
     * @param  array   $rawResults
     * @param  string  $provider
     * @return void
     */
    public function execute(string $query, array $rawResults, string $provider = 'google'): void
    {
        $items = $rawResults['items'] ?? [];
        $resultsCount = is_array($items) ? count($items) : null;

        SearchHistory::create([
            'query'         => $query,
            'results_count' => $resultsCount,
            'provider'      => $provider,
            'results_raw'   => $rawResults,
        ]);
    }
}
