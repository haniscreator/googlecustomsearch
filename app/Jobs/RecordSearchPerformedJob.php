<?php

namespace App\Jobs;

use App\Models\AnalyticsEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Sentry\State\HubInterface;

class RecordSearchPerformedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;              // retry 3 times
    public $backoff = [10, 30, 60]; // interval between retries

    public function __construct(
        public string $query,
        public ?int $resultsCount,
        public ?string $provider = 'google',
    ) {}

    public function handle(): void
    {
        // REMOVE sleep(5) — extremely expensive for workers

        AnalyticsEvent::create([
            'event_type'    => 'search_performed',
            'query'         => $this->query,
            'results_count' => $this->resultsCount,
            'provider'      => $this->provider,
            'meta'          => null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('analytics.job.failed', [
            'query' => $this->query,
            'provider' => $this->provider,
            'message' => $exception->getMessage(),
        ]);

        // Send to Sentry if installed
        if (app()->bound(HubInterface::class)) {
            app(HubInterface::class)->captureException($exception);
        }
    }
}
