<?php

namespace Tests\Unit\Search;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\Search\SearchClient;
use Illuminate\Http\Client\RequestException;

class SearchClientTest extends TestCase
{
    public function test_search_success()
    {
        $fakeResponse = [
            'items' => [['title' => 'cat']],
            'searchInformation' => ['totalResults' => '1']
        ];

        Http::fake([
            'https://www.googleapis.com/*' => Http::response($fakeResponse, 200)
        ]);

        config(['services.search' => [
            'endpoint' => 'https://www.googleapis.com/customsearch/v1',
            'query_param' => 'q',
            'key' => 'xxx',
            'google_cx' => 'yyy',
            'timeout' => 5,
        ]]);

        $client = new SearchClient();
        $result = $client->search('cat');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
    }

    public function test_search_throws_exception_on_500_error()
    {
        Http::fake([
            '*' => Http::response(['error' => 'fail'], 500)
        ]);

        config(['services.search' => [
            'endpoint' => 'https://www.googleapis.com/customsearch/v1',
            'query_param' => 'q',
            'key' => 'xxx',
            'google_cx' => 'yyy',
            'timeout' => 5,
        ]]);

        $this->expectException(RequestException::class);

        $client = new SearchClient();
        $client->search('cat');
    }
}
