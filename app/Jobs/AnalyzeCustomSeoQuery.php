<?php

namespace App\Jobs;

use App\Models\SeoQuery;
use App\Models\Contact;
use App\Models\SeoResult;
use App\Services\CustomSeoAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeCustomSeoQuery implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    
    private SeoQuery $seoQuery;
    private Contact $contact;

    /**
     * Create a new job instance.
     */
    public function __construct(SeoQuery $seoQuery, Contact $contact)
    {
        $this->seoQuery = $seoQuery;
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Début analyse SEO personnalisée pour la requête '{$this->seoQuery->query}' et le contact {$this->contact->id} - {$this->contact->business_name}");
            
            $seoService = new CustomSeoAnalysisService();
            $result = $seoService->analyzeContactForQuery($this->contact, $this->seoQuery);
            
            // Sauvegarder le résultat
            SeoResult::create([
                'seo_query_id' => $this->seoQuery->id,
                'contact_id' => $this->contact->id,
                'query_used' => $this->seoQuery->query,
                'position' => $result['position'],
                'url_found' => $result['url_found'] ?? null,
                'serp_data' => $result['serp_data'] ?? null,
                'total_results' => $result['total_results'] ?? null,
                'competitors' => $result['competitors'] ?? null,
                'found' => $result['found'] ?? false,
                'analyzed_at' => now(),
            ]);
            
            Log::info("Analyse SEO personnalisée terminée avec succès pour la requête '{$this->seoQuery->query}' et le contact {$this->contact->id}");
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'analyse SEO personnalisée pour la requête '{$this->seoQuery->query}' et le contact {$this->contact->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job SEO personnalisé échoué pour la requête '{$this->seoQuery->query}' et le contact {$this->contact->id}: " . $exception->getMessage());
    }
}