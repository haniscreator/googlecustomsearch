<?php

namespace App\Services\Search;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class SearchClient
{
    /**
     * Perform a search against the configured external provider (Google Custom Search).
     *
     * @param  string  $query
     * @param  array   $options
     * @return array
     *
     * @throws RequestException
     */
    public function search(string $query, array $options = []): array
    {
        $config    = config('services.search');
        $endpoint  = $config['endpoint'];
        $queryParam= $config['query_param'] ?? 'q';
        $provider  = $config['provider'] ?? 'google';

        $params = array_merge([
            $queryParam => $query,
        ], $options);

        // Google Custom Search parameters
        if ($provider === 'google') {
            $params['key'] = $config['key'];
            $params['cx']  = $config['google_cx'];
        }

        $start = microtime(true);

        try {
            $response = Http::timeout($config['timeout'] ?? 5)
                ->get($endpoint, $params)
                ->throw();

            $data = $response->json() ?? [];

            // Try to compute a simple result count for logs
            $items        = $data['items'] ?? [];
            $resultsCount = is_array($items) ? count($items) : null;

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            Log::info('search.external.completed', [
                'provider'       => $provider,
                'endpoint'       => $endpoint,
                'query'          => $query,
                'results_count'  => $resultsCount,
                'duration_ms'    => $durationMs,
                'status'         => $response->status(),
            ]);

            return $data;
        } catch (RequestException $e) {
            $durationMs = (int) ((microtime(true) - $start) * 1000);
            $response   = $e->response;

            Log::error('search.external.failed', [
                'provider'    => $provider,
                'endpoint'    => $endpoint,
                'query'       => $query,
                'duration_ms' => $durationMs,
                'status'      => $response?->status(),
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
