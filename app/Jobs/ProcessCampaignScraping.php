<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\GoogleMapsScrapingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignScraping implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 1800;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function handle(GoogleMapsScrapingService $scrapingService): void
    {
        Log::info('Starting scraping for campaign: ' . $this->campaign->id);
        
        try {
            $scrapingService->scrapeCampaign($this->campaign);
            
            Log::info('Scraping completed for campaign: ' . $this->campaign->id);
        } catch (\Exception $e) {
            Log::error('Scraping failed for campaign: ' . $this->campaign->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->campaign->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Job failed permanently for campaign: ' . $this->campaign->id, [
            'error' => $exception->getMessage()
        ]);
        
        $this->campaign->update([
            'status' => 'failed',
            'completed_at' => now()
        ]);
    }
}
