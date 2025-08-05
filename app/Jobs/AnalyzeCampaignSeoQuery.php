<?php

namespace App\Jobs;

use App\Models\SeoQuery;
use App\Models\Contact;
use App\Models\SeoResult;
use App\Services\GoogleCustomSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeCampaignSeoQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes pour analyser toute une campagne
    public $tries = 2;

    protected SeoQuery $seoQuery;
    protected int $maxPages;

    public function __construct(SeoQuery $seoQuery, int $maxPages = 20)
    {
        $this->seoQuery = $seoQuery;
        $this->maxPages = $maxPages;
        
        // Délai aléatoire pour éviter trop de requêtes simultanées
        $this->delay(rand(30, 120)); // 30 secondes à 2 minutes
    }

    public function handle(GoogleCustomSearchService $googleService): void
    {
        Log::info("🚀 Début analyse de campagne SEO pour la requête '{$this->seoQuery->query}'");

        try {
            // Récupérer tous les contacts ciblés avec leurs sites web
            $targetContacts = $this->seoQuery->getTargetContacts()
                ->whereNotNull('website')
                ->get();

            if ($targetContacts->isEmpty()) {
                Log::warning("Aucun contact avec site web trouvé pour la requête '{$this->seoQuery->query}'");
                return;
            }

            // Extraire tous les sites web de la campagne
            $campaignWebsites = $targetContacts->pluck('website')->unique()->toArray();
            
            Log::info("Analyse de {$targetContacts->count()} contacts avec " . count($campaignWebsites) . " sites uniques");

            // Analyser toute la campagne en une seule fois !
            $analysisResult = $googleService->analyzeCampaignQuery(
                $this->seoQuery->query,
                $campaignWebsites,
                $this->seoQuery->location,
                $this->maxPages // Nombre de pages configuré
            );

            $savedResults = 0;
            $notFoundCount = 0;

            // Sauvegarder les résultats pour chaque contact trouvé
            foreach ($targetContacts as $contact) {
                $website = $contact->website;
                
                if (isset($analysisResult['found_websites'][$website])) {
                    // Site trouvé !
                    $siteData = $analysisResult['found_websites'][$website];
                    
                    SeoResult::create([
                        'seo_query_id' => $this->seoQuery->id,
                        'contact_id' => $contact->id,
                        'query_used' => $this->seoQuery->query,
                        'position' => $siteData['position'],
                        'url_found' => $siteData['url'],
                        'found' => true,
                        'serp_data' => [
                            'title' => $siteData['title'],
                            'snippet' => $siteData['snippet'],
                            'page' => $siteData['page'],
                            'method' => 'campaign_analysis',
                            'total_campaign_sites' => count($campaignWebsites),
                            'quota_used_share' => round($analysisResult['quota_used'] / count($targetContacts), 2)
                        ],
                        'analyzed_at' => now(),
                    ]);
                    
                    $savedResults++;
                    Log::info("✅ {$contact->business_name}: position {$siteData['position']}");
                } else {
                    // Site non trouvé dans les premières positions
                    SeoResult::create([
                        'seo_query_id' => $this->seoQuery->id,
                        'contact_id' => $contact->id,
                        'query_used' => $this->seoQuery->query,
                        'position' => null,
                        'url_found' => null,
                        'found' => false,
                        'serp_data' => [
                            'method' => 'campaign_analysis',
                            'reason' => "Not found in top {$this->maxPages * 10} results",
                            'pages_scanned' => $analysisResult['pages_scanned'],
                            'total_campaign_sites' => count($campaignWebsites),
                            'quota_used_share' => round($analysisResult['quota_used'] / count($targetContacts), 2)
                        ],
                        'analyzed_at' => now(),
                    ]);
                    
                    $notFoundCount++;
                    Log::info("❌ {$contact->business_name}: non trouvé dans le top " . ($this->maxPages * 10));
                }
            }

            // Marquer la requête comme exécutée
            $this->seoQuery->markAsExecuted();

            Log::info("🏁 Analyse de campagne terminée : {$savedResults} trouvés, {$notFoundCount} non trouvés, {$analysisResult['quota_used']} requêtes API utilisées");

        } catch (\Exception $e) {
            Log::error("Erreur lors de l'analyse de campagne SEO pour la requête '{$this->seoQuery->query}': " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job d'analyse de campagne SEO échoué pour la requête '{$this->seoQuery->query}': " . $exception->getMessage());
    }
}