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

    public function search(string $query, array $options = []): array
    {
        $provider = 'google';

        $cacheKey = $this->buildCacheKey($provider, $query, $options);
        $ttl = $this->getCacheTtl();

        // Cache::remember closure tested using ->andReturnUsing()
        $results = Cache::remember($cacheKey, $ttl, function () use ($query, $options) {
            return $this->fetchFromProvider($query, $options);
        });

        // Save search history
        $this->storeHistoryAction->execute(
            query: $query,
            rawResults: $results,
            provider: $provider,
        );

        // Dispatch analytics job
        $resultsCount = $this->extractResultsCount($results);
        $this->dispatchAnalyticsEvent($query, $resultsCount, $provider);

        return $results;
    }

    protected function buildCacheKey(string $provider, string $query, array $options = []): string
    {
        $optionsKey = !empty($options) ? md5(json_encode($options)) : 'default';
        return "search:{$provider}:{$optionsKey}:" . md5($query);
    }

    protected function getCacheTtl(): \DateTimeInterface|int
    {
        return now()->addMinutes(5);
    }

    protected function fetchFromProvider(string $query, array $options = []): array
    {
        return $this->client->search($query, $options);
    }

    protected function extractResultsCount(array $results): ?int
    {
        $items = $results['items'] ?? [];
        return is_array($items) ? count($items) : null;
    }

    protected function dispatchAnalyticsEvent(string $query, ?int $resultsCount, string $provider): void
    {
        RecordSearchPerformedJob::dispatch(
            query: $query,
            resultsCount: $resultsCount,
            provider: $provider,
        );
    }
}
