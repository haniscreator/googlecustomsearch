<?php

namespace App\Actions\Search;

use App\Services\Search\SearchService;

class SearchExternalAction
{
    public function __construct(
        protected SearchService $searchService,
    ) {}

    /**
     * Execute a search against the external search provider.
     *
     * @param  string  $query
     * @param  array   $options
     * @return array
     */
    public function execute(string $query, array $options = []): array
    {
        return $this->searchService->search($query, $options);
    }
}
