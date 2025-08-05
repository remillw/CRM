<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GoogleCustomSearchService
{
    private ?string $apiKey;
    private ?string $searchEngineId;
    private string $baseUrl = 'https://www.googleapis.com/customsearch/v1';

    public function __construct()
    {
        $this->apiKey = config('services.google.custom_search_api_key');
        $this->searchEngineId = config('services.google.custom_search_engine_id');
    }

    public function searchWebsitePosition(string $website, string $query, ?string $location = null): array
    {
        if (!$this->apiKey || !$this->searchEngineId) {
            throw new \Exception('Google Custom Search API non configurée. Clés manquantes dans .env');
        }

        // Cache pour éviter d'utiliser le quota trop rapidement
        $cacheKey = 'google_custom_search_' . md5($website . $query . ($location ?? ''));
        
        if (Cache::has($cacheKey)) {
            Log::info("Résultat trouvé en cache pour: {$query}");
            return Cache::get($cacheKey);
        }

        try {
            $searchResults = $this->performSearch($query, $location);
            $result = $this->findWebsiteInResults($website, $searchResults);
            
            // Mettre en cache pendant 4 heures
            Cache::put($cacheKey, $result, 14400);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Erreur Google Custom Search API: " . $e->getMessage());
            throw $e;
        }
    }

    private function performSearch(string $query, ?string $location = null): array
    {
        $params = [
            'key' => $this->apiKey,
            'cx' => $this->searchEngineId,
            'q' => $query,
            'num' => 10, // Maximum 10 résultats par requête
            'start' => 1,
            'hl' => 'fr',
            'gl' => 'fr',
        ];

        // Ajouter la géolocalisation si fournie
        if ($location) {
            $params['cr'] = 'countryFR'; // Restreindre à la France
            $params['q'] = $query . ' ' . $location;
        }

        Log::info("Recherche Google Custom Search pour: {$query}" . ($location ? " à {$location}" : ""));

        $response = Http::timeout(30)->get($this->baseUrl, $params);

        if (!$response->successful()) {
            $error = $response->json()['error'] ?? 'Erreur inconnue';
            throw new \Exception("Erreur API Google: " . json_encode($error));
        }

        $data = $response->json();

        if (!isset($data['items'])) {
            Log::warning("Aucun résultat trouvé pour: {$query}");
            return [];
        }

        $results = [];
        foreach ($data['items'] as $index => $item) {
            $results[] = [
                'position' => $index + 1,
                'title' => $item['title'] ?? '',
                'url' => $item['link'] ?? '',
                'snippet' => $item['snippet'] ?? '',
                'domain' => parse_url($item['link'] ?? '', PHP_URL_HOST)
            ];
        }

        Log::info("Google Custom Search: " . count($results) . " résultats trouvés");
        
        return $results;
    }

    private function findWebsiteInResults(string $targetWebsite, array $results): array
    {
        $targetDomain = $this->extractDomain($targetWebsite);

        foreach ($results as $result) {
            $resultDomain = $this->extractDomain($result['url']);
            
            if ($this->isDomainsMatch($targetDomain, $resultDomain)) {
                Log::info("✅ Site trouvé en position {$result['position']}: {$targetDomain}");
                
                return [
                    'position' => $result['position'],
                    'found' => true,
                    'url_found' => $result['url'],
                    'title_found' => $result['title'],
                    'snippet' => $result['snippet'],
                    'serp_data' => [
                        'total_results_parsed' => count($results),
                        'search_date' => now()->toISOString(),
                        'target_domain' => $targetDomain,
                        'found_domain' => $resultDomain,
                        'method' => 'google_custom_search_api',
                        'api_quota_used' => true
                    ]
                ];
            }
        }

        // Si pas trouvé dans les 10 premiers, chercher dans les suivants
        if (count($results) === 10) {
            // Note: Google Custom Search permet jusqu'à 100 résultats mais coûte plus de quota
            Log::info("Site non trouvé dans les 10 premiers résultats");
        }

        Log::info("❌ Site non trouvé: {$targetDomain}");

        return [
            'position' => null,
            'found' => false,
            'url_found' => null,
            'serp_data' => [
                'total_results_parsed' => count($results),
                'search_date' => now()->toISOString(),
                'target_domain' => $targetDomain,
                'reason' => 'Domain not found in first 10 results',
                'method' => 'google_custom_search_api',
                'api_quota_used' => true
            ]
        ];
    }

    private function extractDomain(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            $domain = $url;
        }
        
        // Retirer www.
        return preg_replace('/^www\./', '', strtolower($domain));
    }

    private function isDomainsMatch(string $domain1, string $domain2): bool
    {
        return strtolower($domain1) === strtolower($domain2);
    }

    public function searchMultiplePages(string $website, string $query, ?string $location = null, int $maxPages = 10): array
    {
        $allResults = [];
        $targetDomain = $this->extractDomain($website);
        
        for ($page = 1; $page <= $maxPages; $page++) {
            $startIndex = ($page - 1) * 10 + 1;
            
            $params = [
                'key' => $this->apiKey,
                'cx' => $this->searchEngineId,
                'q' => $query,
                'num' => 10,
                'start' => $startIndex,
                'hl' => 'fr',
                'gl' => 'fr',
            ];

            if ($location) {
                $params['cr'] = 'countryFR';
                $params['q'] = $query . ' ' . $location;
            }

            try {
                $response = Http::timeout(30)->get($this->baseUrl, $params);
                $data = $response->json();

                if (!isset($data['items'])) {
                    break; // Plus de résultats
                }

                foreach ($data['items'] as $index => $item) {
                    $position = $startIndex + $index;
                    $resultDomain = $this->extractDomain($item['link'] ?? '');
                    
                    if ($this->isDomainsMatch($targetDomain, $resultDomain)) {
                        return [
                            'position' => $position,
                            'found' => true,
                            'url_found' => $item['link'],
                            'title_found' => $item['title'],
                            'snippet' => $item['snippet'] ?? '',
                            'serp_data' => [
                                'search_date' => now()->toISOString(),
                                'method' => 'google_custom_search_deep',
                                'pages_searched' => $page,
                                'api_quota_used' => $page // Chaque page = 1 quota
                            ]
                        ];
                    }
                }

                // Délai entre les pages pour éviter le rate limiting
                sleep(1);

            } catch (\Exception $e) {
                Log::error("Erreur page {$page}: " . $e->getMessage());
                break;
            }
        }

        return [
            'position' => null,
            'found' => false,
            'url_found' => null,
            'serp_data' => [
                'search_date' => now()->toISOString(),
                'method' => 'google_custom_search_deep',
                'pages_searched' => $maxPages,
                'reason' => "Not found in top " . ($maxPages * 10) . " results",
                'api_quota_used' => $maxPages
            ]
        ];
    }

    public function getQuotaStatus(): array
    {
        // Google Custom Search API : 100 requêtes/jour gratuites
        $today = now()->format('Y-m-d');
        $quotaUsed = Cache::remember("google_api_quota_{$today}", 86400, function() {
            return 0;
        });

        return [
            'daily_limit' => 100,
            'used_today' => $quotaUsed,
            'remaining' => max(0, 100 - $quotaUsed),
            'date' => $today
        ];
    }

    public function incrementQuotaUsage(int $requests = 1): void
    {
        $today = now()->format('Y-m-d');
        $currentUsage = Cache::get("google_api_quota_{$today}", 0);
        Cache::put("google_api_quota_{$today}", $currentUsage + $requests, 86400);
    }

    /**
     * Analyse complète d'une campagne : récupère toutes les positions en une seule fois
     * BEAUCOUP plus efficace que d'analyser chaque site individuellement
     */
    public function analyzeCampaignQuery(string $query, array $campaignWebsites, ?string $location = null, int $maxPages = 20): array
    {
        if (!$this->apiKey || !$this->searchEngineId) {
            throw new \Exception('Google Custom Search API non configurée pour l\'analyse de campagne');
        }

        $cacheKey = 'campaign_analysis_' . md5($query . serialize($campaignWebsites) . ($location ?? ''));
        
        if (Cache::has($cacheKey)) {
            Log::info("Analyse de campagne trouvée en cache pour: {$query}");
            return Cache::get($cacheKey);
        }

        Log::info("🚀 Début analyse de campagne pour '{$query}' avec " . count($campaignWebsites) . " sites");

        $allResults = [];
        $foundWebsites = [];
        $quotaUsed = 0;

        // Convertir les websites en domaines pour comparaison rapide
        $targetDomains = [];
        foreach ($campaignWebsites as $website) {
            $domain = $this->extractDomain($website);
            $targetDomains[$domain] = $website;
        }

        try {
            // Scanner page par page jusqu'à trouver tous les sites ou atteindre maxPages
            for ($page = 1; $page <= $maxPages; $page++) {
                $startIndex = ($page - 1) * 10 + 1;
                
                $params = [
                    'key' => $this->apiKey,
                    'cx' => $this->searchEngineId,
                    'q' => $query,
                    'num' => 10,
                    'start' => $startIndex,
                    'hl' => 'fr',
                    'gl' => 'fr',
                ];

                if ($location) {
                    $params['cr'] = 'countryFR';
                    $params['q'] = $query . ' ' . $location;
                }

                Log::info("📄 Analyse page {$page} (positions " . $startIndex . "-" . ($startIndex + 9) . ")");

                $response = Http::timeout(30)->get($this->baseUrl, $params);
                $quotaUsed++;

                if (!$response->successful()) {
                    Log::error("Erreur API page {$page}: " . $response->body());
                    break;
                }

                $data = $response->json();

                if (!isset($data['items']) || empty($data['items'])) {
                    Log::info("Fin des résultats à la page {$page}");
                    break;
                }

                // Analyser chaque résultat de cette page
                foreach ($data['items'] as $index => $item) {
                    $position = $startIndex + $index;
                    $resultUrl = $item['link'] ?? '';
                    $resultDomain = $this->extractDomain($resultUrl);
                    
                    // Stocker tous les résultats pour debug
                    $allResults[] = [
                        'position' => $position,
                        'title' => $item['title'] ?? '',
                        'url' => $resultUrl,
                        'domain' => $resultDomain,
                        'snippet' => $item['snippet'] ?? ''
                    ];

                    // Vérifier si c'est un de nos sites de campagne
                    if (isset($targetDomains[$resultDomain])) {
                        $originalWebsite = $targetDomains[$resultDomain];
                        
                        $foundWebsites[$originalWebsite] = [
                            'website' => $originalWebsite,
                            'domain' => $resultDomain,
                            'position' => $position,
                            'title' => $item['title'] ?? '',
                            'url' => $resultUrl,
                            'snippet' => $item['snippet'] ?? '',
                            'page' => $page
                        ];

                        Log::info("✅ Site trouvé : {$resultDomain} en position {$position}");
                        
                        // Retirer de la liste pour optimiser les recherches suivantes
                        unset($targetDomains[$resultDomain]);
                    }
                }

                // Si on a trouvé tous les sites, pas besoin de continuer
                if (empty($targetDomains)) {
                    Log::info("🎉 Tous les sites de la campagne trouvés, arrêt à la page {$page}");
                    break;
                }

                // Délai entre les pages
                sleep(1);
            }

            // Créer le résultat final
            $result = [
                'query' => $query,
                'location' => $location,
                'found_websites' => $foundWebsites,
                'not_found_websites' => array_values($targetDomains), // Sites non trouvés
                'total_websites_searched' => count($campaignWebsites),
                'websites_found' => count($foundWebsites),
                'pages_scanned' => min($page, $maxPages),
                'total_results' => count($allResults),
                'quota_used' => $quotaUsed,
                'search_date' => now()->toISOString(),
                'method' => 'campaign_analysis',
                'all_results' => $allResults // Pour debug
            ];

            // Mettre en cache pendant 6 heures
            Cache::put($cacheKey, $result, 21600);

            // Incrémenter le quota utilisé
            $this->incrementQuotaUsage($quotaUsed);

            Log::info("🏁 Analyse terminée : {$result['websites_found']}/{$result['total_websites_searched']} sites trouvés, {$quotaUsed} requêtes utilisées");

            return $result;

        } catch (\Exception $e) {
            Log::error("Erreur analyse de campagne: " . $e->getMessage());
            
            // Incrémenter le quota même en cas d'erreur
            if ($quotaUsed > 0) {
                $this->incrementQuotaUsage($quotaUsed);
            }
            
            throw $e;
        }
    }
}