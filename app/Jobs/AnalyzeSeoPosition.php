<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Services\SeoAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeSeoPosition implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    
    private Contact $contact;

    /**
     * Create a new job instance.
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Début analyse SEO pour le contact {$this->contact->id} - {$this->contact->business_name}");
            
            $seoService = new SeoAnalysisService();
            $success = $seoService->analyzeAndSave($this->contact);
            
            if ($success) {
                Log::info("Analyse SEO terminée avec succès pour le contact {$this->contact->id}");
            } else {
                Log::warning("Échec de l'analyse SEO pour le contact {$this->contact->id}");
            }
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'analyse SEO du contact {$this->contact->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job SEO échoué pour le contact {$this->contact->id}: " . $exception->getMessage());
    }
}
