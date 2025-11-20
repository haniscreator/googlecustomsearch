<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\RecordSearchPerformedJob;
use App\Models\AnalyticsEvent;

class RecordSearchPerformedJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_creates_event()
    {
        $job = new RecordSearchPerformedJob(
            query: 'cat',
            resultsCount: 5,
            provider: 'google'
        );

        $job->handle();

        $this->assertDatabaseHas('analytics_events', [
            'event_type' => 'search_performed',
            'query' => 'cat',
        ]);
    }
}
