<?php

namespace App\Console\Commands;

use App\Services\GoogleScraperService;
use Illuminate\Console\Command;

class TestGoogleScraping extends Command
{
    protected $signature = 'seo:test-scraping 
                          {query : La requête de recherche} 
                          {website? : Le site web à rechercher}
                          {--location= : Localisation optionnelle}';

    protected $description = 'Teste le système de scraping Google';

    public function handle(GoogleScraperService $scraperService): int
    {
        $query = $this->argument('query');
        $website = $this->argument('website');
        $location = $this->option('location');

        $this->info("🔍 Test de scraping Google pour: {$query}");
        if ($location) {
            $this->info("📍 Localisation: {$location}");
        }

        try {
            if ($website) {
                // Test avec recherche de position d'un site spécifique
                $this->info("🎯 Recherche de position pour: {$website}");
                $result = $scraperService->searchWebsitePosition($website, $query, $location);
                
                $this->line('');
                $this->info('📊 Résultats:');
                $this->table(['Propriété', 'Valeur'], [
                    ['Site recherché', $website],
                    ['Trouvé', $result['found'] ? '✅ Oui' : '❌ Non'],
                    ['Position', $result['position'] ?? 'Non trouvé'],
                    ['URL trouvée', $result['url_found'] ?? 'N/A'],
                    ['Titre trouvé', $result['title_found'] ?? 'N/A'],
                ]);
                
            } else {
                // Test de recherche générale
                $results = $scraperService->testSearch($query);
                
                $this->line('');
                $this->info('📊 Premiers résultats trouvés:');
                
                $tableData = [];
                foreach (array_slice($results, 0, 10) as $result) {
                    $tableData[] = [
                        $result['position'],
                        substr($result['title'], 0, 50) . '...',
                        $result['domain']
                    ];
                }
                
                $this->table(['Position', 'Titre', 'Domaine'], $tableData);
                $this->info("Total: " . count($results) . " résultats trouvés");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }

        $this->info("✅ Test terminé avec succès!");
        return 0;
    }
}