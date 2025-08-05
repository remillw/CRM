<?php

namespace App\Console\Commands;

use App\Services\GoogleCustomSearchService;
use Illuminate\Console\Command;

class TestGoogleCustomSearch extends Command
{
    protected $signature = 'seo:test-google-api 
                          {query : La requête de recherche} 
                          {website? : Le site web à rechercher}
                          {--location= : Localisation optionnelle}
                          {--deep : Recherche approfondie (plusieurs pages)}';

    protected $description = 'Teste l\'API Google Custom Search';

    public function handle(GoogleCustomSearchService $googleService): int
    {
        $query = $this->argument('query');
        $website = $this->argument('website');
        $location = $this->option('location');
        $deep = $this->option('deep');

        $this->info("🔍 Test de Google Custom Search API pour: {$query}");
        if ($location) {
            $this->info("📍 Localisation: {$location}");
        }

        // Vérifier le quota
        $quota = $googleService->getQuotaStatus();
        $this->info("📊 Quota API: {$quota['used_today']}/{$quota['daily_limit']} utilisé aujourd'hui");

        if ($quota['remaining'] <= 0) {
            $this->error("❌ Quota API épuisé pour aujourd'hui !");
            return 1;
        }

        try {
            if ($website) {
                // Test avec recherche de position d'un site spécifique
                $this->info("🎯 Recherche de position pour: {$website}");
                
                if ($deep) {
                    $result = $googleService->searchMultiplePages($website, $query, $location, 5);
                } else {
                    $result = $googleService->searchWebsitePosition($website, $query, $location);
                }
                
                $this->line('');
                $this->info('📊 Résultats:');
                $this->table(['Propriété', 'Valeur'], [
                    ['Site recherché', $website],
                    ['Trouvé', $result['found'] ? '✅ Oui' : '❌ Non'],
                    ['Position', $result['position'] ?? 'Non trouvé'],
                    ['URL trouvée', $result['url_found'] ?? 'N/A'],
                    ['Titre trouvé', substr($result['title_found'] ?? 'N/A', 0, 50) . '...'],
                    ['Méthode', $result['serp_data']['method'] ?? 'N/A'],
                    ['Quota utilisé', $result['serp_data']['api_quota_used'] ?? 'N/A'],
                ]);
                
            } else {
                // Test de recherche générale (première page seulement)
                $this->error("❌ La recherche générale n'est pas encore implémentée pour ce test.");
                $this->info("💡 Utilisez: php artisan seo:test-google-api \"pizzeria lyon\" \"https://example.com\"");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'API non configurée')) {
                $this->line('');
                $this->warn('🔧 Configuration requise:');
                $this->line('1. Ajoutez dans votre fichier .env:');
                $this->line('   GOOGLE_CUSTOM_SEARCH_API_KEY=votre_cle_api');
                $this->line('   GOOGLE_CUSTOM_SEARCH_ENGINE_ID=votre_search_engine_id');
                $this->line('');
                $this->line('2. Guide complet: https://developers.google.com/custom-search/v1/introduction');
            }
            
            return 1;
        }

        // Afficher le nouveau quota
        $newQuota = $googleService->getQuotaStatus();
        $this->info("📊 Nouveau quota: {$newQuota['used_today']}/{$newQuota['daily_limit']} (reste: {$newQuota['remaining']})");

        $this->info("✅ Test terminé avec succès!");
        return 0;
    }
}