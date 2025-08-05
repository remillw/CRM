<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\SeoQuery;
use App\Models\SeoResult;
use App\Services\GoogleScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeGoogleResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    public $backoff = [10, 30, 60]; // Délai entre les tentatives

    protected Contact $contact;
    protected SeoQuery $seoQuery;

    public function __construct(Contact $contact, SeoQuery $seoQuery)
    {
        $this->contact = $contact;
        $this->seoQuery = $seoQuery;
        
        // Délai aléatoire pour éviter trop de requêtes simultanées
        $this->delay(rand(5, 30));
    }

    public function handle(GoogleScraperService $scraperService): void
    {
        Log::info("Début scraping Google pour contact {$this->contact->id} avec requête '{$this->seoQuery->query}'");

        try {
            $result = $scraperService->searchWebsitePosition(
                $this->contact->website,
                $this->seoQuery->query,
                $this->seoQuery->location
            );

            // Sauvegarder le résultat
            SeoResult::create([
                'contact_id' => $this->contact->id,
                'seo_query_id' => $this->seoQuery->id,
                'position' => $result['position'],
                'found' => $result['found'],
                'url_found' => $result['url_found'] ?? null,
                'query_used' => $this->seoQuery->query,
                'serp_data' => $result['serp_data'] ?? null,
                'analyzed_at' => now(),
            ]);

            Log::info("Scraping terminé pour {$this->contact->business_name}: " . 
                ($result['found'] ? "Position {$result['position']}" : "Non trouvé"));

        } catch (\Exception $e) {
            Log::error("Erreur scraping pour contact {$this->contact->id}: " . $e->getMessage());
            
            // Sauvegarder l'erreur
            SeoResult::create([
                'contact_id' => $this->contact->id,
                'seo_query_id' => $this->seoQuery->id,
                'position' => null,
                'found' => false,
                'query_used' => $this->seoQuery->query,
                'serp_data' => ['error' => $e->getMessage()],
                'analyzed_at' => now(),
            ]);

            throw $e; // Pour déclencher les retry si configuré
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job de scraping échoué définitivement pour contact {$this->contact->id}: " . $exception->getMessage());
    }
}