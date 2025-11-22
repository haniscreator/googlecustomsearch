<?php

namespace Tests\Unit\Search;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Bus;
use Mockery;
use App\Services\Search\SearchService;
use App\Services\Search\SearchClient;
use App\Actions\Search\StoreHistoryAction;
use App\Jobs\RecordSearchPerformedJob;

class SearchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cache_miss_calls_client_and_dispatches_job()
    {
        Bus::fake();

        $query = 'cat';
        $options = [];
        $expected = ['items' => [['title' => 'result']]];
        $expectedResultsCount = is_array($expected['items']) ? count($expected['items']) : null;
        $expectedSuffix = md5($query);

        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('search')
            ->once()
            ->with($query, $options)
            ->andReturn($expected);

        $storeHistory = Mockery::mock(StoreHistoryAction::class);
        $storeHistory->shouldReceive('execute')
            ->once()
            ->withArgs(function ($q, $raw, $provider) use ($query, $expected) {
                return $q === $query && $raw === $expected && $provider === 'google';
            });

        // Assert cache key shape and run the closure to simulate cache miss
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $ttl, $closure) use ($expectedSuffix) {
                // key should end with md5(query) and ttl should be a DateTimeInterface or integer
                $keyOk = \str_ends_with($key, $expectedSuffix) && \str_starts_with($key, 'search:google:');
                $ttlOk = $ttl instanceof \DateTimeInterface || is_int($ttl);
                $closureOk = is_callable($closure);
                return $keyOk && $ttlOk && $closureOk;
            })
            ->andReturnUsing(fn ($key, $ttl, $closure) => $closure());

        $service = new SearchService($client, $storeHistory);

        $result = $service->search($query);

        $this->assertSame($expected, $result);

        // Assert the job was dispatched with correct payload
        Bus::assertDispatched(RecordSearchPerformedJob::class, function ($job) use ($query, $expectedResultsCount) {
            return $job->query === $query
                && $job->resultsCount === $expectedResultsCount
                && $job->provider === 'google';
        });
    }

    public function test_cache_hit_does_not_call_client_but_dispatches_job()
    {
        Bus::fake();

        $query = 'dog';
        $cached = ['items' => [['title' => 'cached']]];
        $expectedResultsCount = is_array($cached['items']) ? count($cached['items']) : null;
        $expectedSuffix = md5($query);

        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('search')->never();

        $storeHistory = Mockery::mock(StoreHistoryAction::class);
        $storeHistory->shouldReceive('execute')->once();

        // Assert cache key shape and return cached result (simulate cache hit)
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key, $ttl, $closure) use ($expectedSuffix) {
                $keyOk = \str_ends_with($key, $expectedSuffix) && \str_starts_with($key, 'search:google:');
                $ttlOk = $ttl instanceof \DateTimeInterface || is_int($ttl);
                // On cache hit the closure should still be provided but won't be executed by our mock
                $closureOk = is_callable($closure);
                return $keyOk && $ttlOk && $closureOk;
            })
            ->andReturn($cached);

        $service = new SearchService($client, $storeHistory);

        $result = $service->search($query);

        $this->assertSame($cached, $result);

        // Assert the job was dispatched with correct payload even when result comes from cache
        Bus::assertDispatched(RecordSearchPerformedJob::class, function ($job) use ($query, $expectedResultsCount) {
            return $job->query === $query
                && $job->resultsCount === $expectedResultsCount
                && $job->provider === 'google';
        });
    }
}
