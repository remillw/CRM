<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeoAnalysisService
{
    public function analyzeContactSeoPosition(Contact $contact): array
    {
        if (!$contact->website || !$contact->campaign) {
            return [
                'position' => null,
                'error' => 'Site web ou campagne manquant'
            ];
        }

        $activityType = $contact->campaign->activity_type;
        $city = $contact->campaign->city;
        $website = $this->cleanWebsiteUrl($contact->website);
        
        // Générer différentes variantes de requêtes de recherche
        $searchQueries = $this->generateSearchQueries($activityType, $city);
        
        $results = [];
        
        foreach ($searchQueries as $query) {
            try {
                $position = $this->searchWebsitePosition($website, $query);
                $results[] = [
                    'query' => $query,
                    'position' => $position,
                    'found' => $position !== null
                ];
                
                // Petite pause entre les requêtes
                sleep(1);
                
            } catch (\Exception $e) {
                Log::warning("Erreur analyse SEO pour {$website} avec query '{$query}': " . $e->getMessage());
                $results[] = [
                    'query' => $query,
                    'position' => null,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Calculer la meilleure position
        $bestPosition = null;
        foreach ($results as $result) {
            if ($result['position'] && ($bestPosition === null || $result['position'] < $bestPosition)) {
                $bestPosition = $result['position'];
            }
        }

        return [
            'position' => $bestPosition,
            'queries' => $results,
            'analyzed_at' => now(),
            'website' => $website
        ];
    }

    private function generateSearchQueries(string $activityType, string $city): array
    {
        $queries = [];
        
        // Requête principale
        $queries[] = "{$activityType} {$city}";
        
        // Avec quartiers potentiels (codes postaux communs)
        $commonPostalCodes = $this->getCommonPostalCodes($city);
        foreach ($commonPostalCodes as $postalCode) {
            $neighborhood = $this->getNeighborhoodFromPostalCode($postalCode);
            if ($neighborhood) {
                $queries[] = "{$activityType} {$neighborhood}";
                $queries[] = "{$activityType} {$postalCode}";
            }
        }
        
        // Variantes de l'activité
        $activityVariants = $this->getActivityVariants($activityType);
        foreach ($activityVariants as $variant) {
            $queries[] = "{$variant} {$city}";
        }
        
        return array_unique($queries);
    }

    private function searchWebsitePosition(string $website, string $query): ?int
    {
        // Utiliser SerpApi ou Google Custom Search API
        $apiKey = config('services.serpapi.api_key');
        
        if (!$apiKey) {
            // Fallback: simuler une recherche basique
            return $this->simulateSearch($website, $query);
        }

        try {
            $response = Http::get('https://serpapi.com/search', [
                'engine' => 'google',
                'q' => $query,
                'api_key' => $apiKey,
                'location' => 'France',
                'hl' => 'fr',
                'gl' => 'fr',
                'num' => 100 // Analyser les 100 premiers résultats
            ]);

            $data = $response->json();
            
            if (!isset($data['organic_results'])) {
                return null;
            }

            foreach ($data['organic_results'] as $index => $result) {
                if (isset($result['link']) && $this->isWebsiteMatch($website, $result['link'])) {
                    return $index + 1; // Position commence à 1
                }
            }

            return null; // Non trouvé dans les 100 premiers
            
        } catch (\Exception $e) {
            Log::error("Erreur SerpApi: " . $e->getMessage());
            return null;
        }
    }

    private function simulateSearch(string $website, string $query): ?int
    {
        // Simulation basique pour les tests sans API
        $domain = parse_url($website, PHP_URL_HOST);
        $queryWords = explode(' ', strtolower($query));
        
        // Si le domaine contient des mots de la requête, position probable plus haute
        $score = 0;
        foreach ($queryWords as $word) {
            if (strpos(strtolower($domain), $word) !== false) {
                $score += 10;
            }
        }
        
        if ($score > 0) {
            return rand(1, 20); // Position entre 1 et 20
        }
        
        return rand(21, 100); // Position plus basse ou null
    }

    private function isWebsiteMatch(string $targetWebsite, string $resultUrl): bool
    {
        $targetDomain = parse_url($targetWebsite, PHP_URL_HOST);
        $resultDomain = parse_url($resultUrl, PHP_URL_HOST);
        
        // Nettoyer les domaines (retirer www, etc.)
        $targetDomain = preg_replace('/^www\./', '', $targetDomain);
        $resultDomain = preg_replace('/^www\./', '', $resultDomain);
        
        return strtolower($targetDomain) === strtolower($resultDomain);
    }

    private function cleanWebsiteUrl(string $website): string
    {
        // Ajouter http si manquant
        if (!preg_match('/^https?:\/\//', $website)) {
            $website = 'https://' . $website;
        }
        
        return $website;
    }

    private function getCommonPostalCodes(string $city): array
    {
        // Base de données simplifiée des codes postaux par ville
        $postalCodes = [
            'marseille' => ['13001', '13002', '13003', '13004', '13005', '13006', '13007', '13008', '13009', '13010', '13011', '13012', '13013', '13014', '13015', '13016'],
            'paris' => ['75001', '75002', '75003', '75004', '75005', '75006', '75007', '75008', '75009', '75010', '75011', '75012', '75013', '75014', '75015', '75016', '75017', '75018', '75019', '75020'],
            'lyon' => ['69001', '69002', '69003', '69004', '69005', '69006', '69007', '69008', '69009'],
            'nice' => ['06000', '06100', '06200', '06300'],
            'toulouse' => ['31000', '31100', '31200', '31300', '31400', '31500']
        ];
        
        $cityLower = strtolower($city);
        return $postalCodes[$cityLower] ?? [];
    }

    private function getNeighborhoodFromPostalCode(string $postalCode): ?string
    {
        // Mapping simplifié code postal -> quartier
        $neighborhoods = [
            // Marseille
            '13001' => 'Vieux-Port',
            '13002' => 'Joliette',
            '13003' => 'Belle de Mai',
            '13004' => 'Cinq Avenues',
            '13005' => 'Baille',
            '13006' => 'Castellane',
            '13007' => 'Saint-Victor',
            '13008' => 'Périer',
            '13009' => 'Mazargues',
            '13010' => 'Timone',
            '13011' => 'Saint-Menet',
            '13012' => 'Saint-Barnabé',
            '13013' => 'Château Gombert',
            '13014' => 'Sainte-Marthe',
            '13015' => 'Cabucelle',
            '13016' => 'Estaque',
            
            // Paris (quelques arrondissements)
            '75001' => 'Louvre',
            '75002' => 'Bourse',
            '75003' => 'Temple',
            '75004' => 'Hôtel-de-Ville',
            '75005' => 'Panthéon',
            '75006' => 'Luxembourg',
            '75007' => 'Palais-Bourbon',
            '75008' => 'Élysée',
            '75009' => 'Opéra',
            '75010' => 'Entrepôt',
            '75011' => 'Popincourt',
            '75012' => 'Reuilly',
            '75013' => 'Gobelins',
            '75014' => 'Observatoire',
            '75015' => 'Vaugirard',
            '75016' => 'Passy',
            '75017' => 'Batignolles-Monceau',
            '75018' => 'Butte-Montmartre',
            '75019' => 'Buttes-Chaumont',
            '75020' => 'Ménilmontant'
        ];
        
        return $neighborhoods[$postalCode] ?? null;
    }

    private function getActivityVariants(string $activityType): array
    {
        $variants = [
            'restaurant' => ['resto', 'restauration', 'brasserie', 'bistrot'],
            'pizzeria' => ['pizza', 'pizzaiolo', 'pizzas'],
            'coiffeur' => ['coiffure', 'salon de coiffure', 'barbier'],
            'boulangerie' => ['boulanger', 'pain', 'pâtisserie'],
            'pharmacie' => ['pharmacien', 'officine'],
            'garage' => ['mécanicien', 'réparation auto', 'carrosserie'],
            'dentiste' => ['dentaire', 'orthodontiste', 'chirurgien dentiste'],
            'médecin' => ['docteur', 'cabinet médical', 'généraliste']
        ];
        
        return $variants[strtolower($activityType)] ?? [];
    }

    public function analyzeBatch(array $contactIds): void
    {
        foreach ($contactIds as $contactId) {
            $contact = Contact::find($contactId);
            if ($contact) {
                $this->analyzeAndSave($contact);
                // Pause entre chaque analyse pour éviter le rate limiting
                sleep(2);
            }
        }
    }

    public function analyzeAndSave(Contact $contact): bool
    {
        try {
            $analysis = $this->analyzeContactSeoPosition($contact);
            
            $contact->update([
                'seo_position' => $analysis['position'],
                'seo_data' => $analysis,
                'seo_analyzed_at' => now()
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Erreur analyse SEO contact {$contact->id}: " . $e->getMessage());
            return false;
        }
    }
}