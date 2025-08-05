<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailSend;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailCampaign implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 3600;

    public function __construct(
        public EmailCampaign $campaign
    ) {}

    public function handle(): void
    {
        Log::info('Starting email campaign: ' . $this->campaign->id);
        
        $this->campaign->update([
            'status' => 'sending',
            'sent_at' => now()
        ]);

        $contacts = $this->campaign->list->contacts()->withEmail()->get();
        $sentCount = 0;

        foreach ($contacts as $contact) {
            try {
                $emailSend = EmailSend::create([
                    'campaign_id' => $this->campaign->id,
                    'contact_id' => $contact->id,
                    'status' => 'pending'
                ]);

                SendSingleEmail::dispatch($emailSend);
                $sentCount++;
                
                // Limiter le débit (1 email par seconde)
                sleep(1);
                
            } catch (\Exception $e) {
                Log::error('Failed to queue email for contact: ' . $contact->id, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->campaign->update([
            'status' => 'sent',
            'sent_count' => $sentCount
        ]);

        Log::info('Email campaign completed: ' . $this->campaign->id . ' - Sent: ' . $sentCount);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Email campaign job failed: ' . $this->campaign->id, [
            'error' => $exception->getMessage()
        ]);
        
        $this->campaign->update(['status' => 'failed']);
    }
}
