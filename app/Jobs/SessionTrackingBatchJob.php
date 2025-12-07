<?php

namespace App\Jobs;

use App\Models\SessionTracking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SessionTrackingBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $trackingData;

    public function __construct(array $trackingData)
    {
        $this->trackingData = $trackingData;
    }

    public function handle(): void
    {
        SessionTracking::query()->create($this->trackingData);
    }
}
