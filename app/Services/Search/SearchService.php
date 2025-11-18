<?php

namespace App\Services\Search;

use App\Actions\Search\StoreHistoryAction;
use App\Jobs\RecordSearchPerformedJob;

class SearchService
{
    public function __construct(
        protected SearchClient $client,
        protected StoreHistoryAction $storeHistoryAction,
    ) {}

    /**
     * Perform a search using the external search provider.
     */
    public function search(string $query, array $options = []): array
    {
        // 1. Call external provider
        $results = $this->client->search($query, $options);

        // 2. Store history (sync for now)
        $this->storeHistoryAction->execute(
            query: $query,
            rawResults: $results,
            provider: 'google',
        );

        // 3. Publish analytics event (async via queue)
        $items = $results['items'] ?? [];
        $resultsCount = is_array($items) ? count($items) : null;

        RecordSearchPerformedJob::dispatch(
            query: $query,
            resultsCount: $resultsCount,
            provider: 'google',
        );

        return $results;
    }

}
