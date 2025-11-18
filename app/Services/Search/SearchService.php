<?php

namespace App\Services\Search;

class SearchService
{
    public function __construct(
        protected SearchClient $client,
    ) {}

    /**
     * Perform a search using the external search provider.
     *
     * @param  string  $query
     * @param  array   $options  Extra query params (page, size, etc.)
     * @return array             Decoded JSON response
     */
    public function search(string $query, array $options = []): array
    {
        // Later we will:
        // - add caching
        // - call StoreHistoryAction
        // - emit analytics events
        // For now, we just proxy to the client.
        return $this->client->search($query, $options);
    }
}
