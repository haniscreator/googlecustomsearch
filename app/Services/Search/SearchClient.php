<?php

namespace App\Services\Search;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class SearchClient
{
    /**
     * Call the external search provider (currently Google Custom Search).
     *
     * @param  string  $query
     * @param  array   $options  Extra query params (page, size, etc.)
     * @return array             Decoded JSON response
     *
     * @throws RequestException
     */
    public function search(string $query, array $options = []): array
    {
        $config = config('services.search');

        $endpoint    = $config['endpoint'];
        $queryParam  = $config['query_param'] ?? 'q';
        $provider    = $config['provider'] ?? 'google';

        $params = array_merge([
            $queryParam => $query,
        ], $options);

        // For Google Custom Search:
        if ($provider === 'google') {
            $params['key'] = $config['key'];
            $params['cx']  = $config['google_cx'];
        }

        $response = Http::timeout($config['timeout'] ?? 5)
            ->get($endpoint, $params)
            ->throw(); // throws RequestException on 4xx/5xx

        return $response->json() ?? [];
    }
}
