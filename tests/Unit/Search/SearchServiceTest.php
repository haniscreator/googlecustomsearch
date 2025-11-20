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

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(fn ($key, $ttl, $closure) => $closure());

        $service = new SearchService($client, $storeHistory);

        $result = $service->search($query);

        $this->assertSame($expected, $result);

        Bus::assertDispatched(RecordSearchPerformedJob::class);
    }

    public function test_cache_hit_does_not_call_client_but_dispatches_job()
    {
        Bus::fake();

        $query = 'dog';
        $cached = ['items' => [['title' => 'cached']]];

        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('search')->never();

        $storeHistory = Mockery::mock(StoreHistoryAction::class);
        $storeHistory->shouldReceive('execute')->once();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($cached);

        $service = new SearchService($client, $storeHistory);

        $result = $service->search($query);

        $this->assertSame($cached, $result);

        Bus::assertDispatched(RecordSearchPerformedJob::class);
    }
}
