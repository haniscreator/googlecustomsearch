<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AnalyticsEvent;

class RecordSearchPerformedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $query,
        public ?int $resultsCount,
        public ?string $provider = 'google',
    ) {}

    /**
     * Handle the job.
     * Later we will:
     *  - write to Analytics DB
     *  - update aggregates
     */
    public function handle(): void
    {
        AnalyticsEvent::create([
            'event_type'    => 'search_performed',
            'query'         => $this->query,
            'results_count' => $this->resultsCount,
            'provider'      => $this->provider,
            'meta'          => null, // or [] if you want
        ]);
    }
    
}
