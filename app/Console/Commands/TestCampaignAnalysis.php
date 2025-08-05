<?php

namespace App\Console\Commands;

use App\Services\GoogleCustomSearchService;
use Illuminate\Console\Command;

class TestCampaignAnalysis extends Command
{
    protected $signature = 'seo:test-campaign 
                          {query : La requête de recherche}
                          {websites* : Les sites web à rechercher (séparés par des espaces)}
                          {--location= : Localisation optionnelle}
                          {--pages=5 : Nombre de pages à scanner (défaut: 5)}';

    protected $description = 'Teste l\'analyse de campagne complète';

    public function handle(GoogleCustomSearchService $googleService): int
    {
        $query = $this->argument('query');
        $websites = $this->argument('websites');
        $location = $this->option('location');
        $maxPages = (int) $this->option('pages');

        $this->info("🚀 Test d'analyse de campagne pour: {$query}");
        $this->info("📊 Sites à rechercher: " . count($websites));
        $this->info("📄 Pages à scanner: {$maxPages} (positions 1-" . ($maxPages * 10) . ")");
        
        if ($location) {
            $this->info("📍 Localisation: {$location}");
        }

        // Vérifier le quota
        try {
            $quota = $googleService->getQuotaStatus();
            $this->info("📊 Quota API: {$quota['used_today']}/{$quota['daily_limit']} (reste: {$quota['remaining']})");

            if ($quota['remaining'] < $maxPages) {
                $this->warn("⚠️  Quota insuffisant ! Il vous faut {$maxPages} requêtes mais il ne reste que {$quota['remaining']}");
                if (!$this->confirm('Continuer quand même ?')) {
                    return 1;
                }
            }

            $this->line('');
            $this->info("Sites recherchés:");
            foreach ($websites as $i => $website) {
                $this->line("  " . ($i + 1) . ". {$website}");
            }

            $this->line('');
            $this->info("🔍 Lancement de l'analyse...");

            // Lancer l'analyse de campagne
            $result = $googleService->analyzeCampaignQuery($query, $websites, $location, $maxPages);

            $this->line('');
            $this->info("📊 RÉSULTATS DE L'ANALYSE:");
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            // Résumé
            $this->table(['Métrique', 'Valeur'], [
                ['Sites recherchés', $result['total_websites_searched']],
                ['Sites trouvés', $result['websites_found']],
                ['Pages scannées', $result['pages_scanned'] . "/" . $maxPages],
                ['Total résultats analysés', $result['total_results']],
                ['Requêtes API utilisées', $result['quota_used']],
                ['Efficacité', round(($result['websites_found'] / $result['total_websites_searched']) * 100, 1) . '%'],
            ]);

            if (!empty($result['found_websites'])) {
                $this->line('');
                $this->info("✅ SITES TROUVÉS:");
                
                $foundData = [];
                foreach ($result['found_websites'] as $website => $data) {
                    $foundData[] = [
                        'Site' => $data['domain'],
                        'Position' => $data['position'],
                        'Page' => $data['page'],
                        'Titre' => substr($data['title'], 0, 50) . '...'
                    ];
                }
                
                // Trier par position
                usort($foundData, function($a, $b) {
                    return $a['Position'] <=> $b['Position'];
                });
                
                $this->table(['Site', 'Position', 'Page', 'Titre'], $foundData);
            }

            if (!empty($result['not_found_websites'])) {
                $this->line('');
                $this->warn("❌ SITES NON TROUVÉS (dans le top " . ($maxPages * 10) . "):");
                foreach ($result['not_found_websites'] as $website) {
                    $this->line("  • {$website}");
                }
            }

            // Nouveau quota
            $newQuota = $googleService->getQuotaStatus();
            $this->line('');
            $this->info("📊 Quota après analyse: {$newQuota['used_today']}/{$newQuota['daily_limit']} (reste: {$newQuota['remaining']})");

            // Calcul d'efficacité
            $this->line('');
            $this->info("💡 ANALYSE D'EFFICACITÉ:");
            $this->line("  • Méthode classique: " . count($websites) . " requêtes API (1 par site)");
            $this->line("  • Méthode campagne: {$result['quota_used']} requêtes API");
            $economie = count($websites) - $result['quota_used'];
            $pourcentage = round(($economie / count($websites)) * 100, 1);
            $this->line("  • 🎉 Économie: {$economie} requêtes ({$pourcentage}%)");

        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'API non configurée')) {
                $this->line('');
                $this->warn('🔧 Configuration requise:');
                $this->line('Ajoutez dans votre .env:');
                $this->line('GOOGLE_CUSTOM_SEARCH_API_KEY=votre_cle');
                $this->line('GOOGLE_CUSTOM_SEARCH_ENGINE_ID=votre_id');
            }
            
            return 1;
        }

        $this->info("✅ Test terminé avec succès!");
        return 0;
    }
}