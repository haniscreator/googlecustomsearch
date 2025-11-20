<?php

namespace Tests\Unit\Actions;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Actions\Search\StoreHistoryAction;
use App\Models\SearchHistory;

class StoreHistoryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_creates_record()
    {
        $action = new StoreHistoryAction();

        $raw = ['items' => [['title' => 'abc']]];

        $action->execute('hello', $raw, 'google');

        $this->assertDatabaseHas('search_histories', [
            'query' => 'hello',
            'provider' => 'google',
        ]);
    }
}
