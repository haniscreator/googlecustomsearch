<?php

namespace App\Services\Search;

use App\Actions\Search\StoreHistoryAction;
use App\Jobs\RecordSearchPerformedJob;
use Illuminate\Support\Facades\Cache;

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
        $provider = 'google'; // later can come from config

        // Build a stable cache key: provider + query + options hash
        $optionsKey = !empty($options) ? md5(json_encode($options)) : 'default';
        $cacheKey   = "search:{$provider}:{$optionsKey}:" . md5($query);

        // TTL for cached results (e.g. 5 minutes)
        $ttl = now()->addMinutes(5);

        // 1. Try cache first, if miss, call external provider and store
        $results = Cache::remember($cacheKey, $ttl, function () use ($query, $options) {
            return $this->client->search($query, $options);
        });

        // 2. Store history (we still want to log that a user searched,
        //    even if the result came from cache)
        $this->storeHistoryAction->execute(
            query: $query,
            rawResults: $results,
            provider: $provider,
        );

        // 3. Publish analytics event
        $items        = $results['items'] ?? [];
        $resultsCount = is_array($items) ? count($items) : null;

        RecordSearchPerformedJob::dispatch(
            query: $query,
            resultsCount: $resultsCount,
            provider: $provider,
        );

        return $results;
    }


}
