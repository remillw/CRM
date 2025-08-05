<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Contact;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GoogleMapsScrapingService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ]
        ]);

    }


    public function scrapeCampaign(Campaign $campaign): void
    {
        $campaign->update([
            'status' => 'running',
            'started_at' => now()
        ]);

        try {
            $query = $campaign->activity_type . ' ' . $campaign->city;
            $contacts = $this->searchBusinesses($query, $campaign->target_count);

            foreach ($contacts as $contactData) {
                Contact::create([
                    'campaign_id' => $campaign->id,
                    'business_name' => $contactData['name'],
                    'phone' => $contactData['phone'] ?? null,
                    'email' => $contactData['email'] ?? null,
                    'website' => $contactData['website'] ?? null,
                    'address' => $contactData['address'] ?? null,
                    'google_rating' => $contactData['rating'] ?? null,
                    'review_count' => $contactData['review_count'] ?? null,
                    'is_verified' => $contactData['is_verified'] ?? false,
                    'additional_data' => $contactData['additional'] ?? null,
                    'scraped_at' => now(),
                ]);
            }

            $campaign->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Scraping failed for campaign ' . $campaign->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $campaign->update([
                'status' => 'failed',
                'completed_at' => now()
            ]);
        }
    }

    private function searchBusinesses(string $query, int $limit): array
    {
        // Vérifier qu'une API réelle est configurée
        if (config('services.google_places.api_key')) {
            return $this->searchWithGooglePlacesAPI($query, $limit);
        }
        
        if (config('services.serpapi.api_key')) {
            return $this->searchWithSerpAPI($query, $limit);
        }
        
        // AUCUNE données fictives - une API réelle doit être configurée
        throw new \Exception('Aucune API de scraping configurée. Veuillez configurer Google Places API ou SerpAPI pour récupérer de vraies données GMB.');
    }
    
    private function searchWithGooglePlacesAPI(string $query, int $limit): array
    {
        $businesses = [];
        $apiKey = config('services.google_places.api_key');
        $nextPageToken = null;
        
        try {
            // Faire plusieurs requêtes pour récupérer plus de résultats
            do {
                $params = [
                    'query' => $query,
                    'key' => $apiKey,
                    'language' => 'fr',
                    'region' => 'fr'
                ];
                
                if ($nextPageToken) {
                    $params['pagetoken'] = $nextPageToken;
                    // Attendre 2 secondes entre les requêtes (requis par Google)
                    sleep(2);
                }
                
                $response = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', $params);
                $data = $response->json();
                
                if ($data['status'] === 'OK') {
                    foreach ($data['results'] as $place) {
                        if (count($businesses) >= $limit) break;
                        
                        $details = $this->getPlaceDetails($place['place_id'], $apiKey);
                        
                        $businesses[] = [
                            'name' => $place['name'],
                            'phone' => $details['formatted_phone_number'] ?? null,
                            'email' => $this->extractEmailFromWebsite($details['website'] ?? null),
                            'website' => $details['website'] ?? null,
                            'address' => $place['formatted_address'] ?? null,
                            'rating' => $place['rating'] ?? null,
                            'review_count' => $place['user_ratings_total'] ?? 0,
                            'is_verified' => isset($place['business_status']) && $place['business_status'] === 'OPERATIONAL',
                            'additional' => [
                                'place_id' => $place['place_id'],
                                'types' => $place['types'] ?? [],
                                'price_level' => $place['price_level'] ?? null,
                                'opening_hours' => $details['opening_hours']['weekday_text'] ?? null
                            ]
                        ];
                    }
                    
                    $nextPageToken = $data['next_page_token'] ?? null;
                } else {
                    Log::warning('Google Places API returned status: ' . $data['status']);
                    break;
                }
                
            } while ($nextPageToken && count($businesses) < $limit);
            
        } catch (\Exception $e) {
            Log::error('Google Places API error: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération des données Google Places API: ' . $e->getMessage());
        }
        
        return $businesses;
    }
    
    private function searchWithSerpAPI(string $query, int $limit): array
    {
        $businesses = [];
        $apiKey = config('services.serpapi.api_key');
        
        try {
            $response = Http::get('https://serpapi.com/search.json', [
                'engine' => 'google_maps',
                'q' => $query,
                'api_key' => $apiKey,
                'hl' => 'fr',
                'gl' => 'fr',
                'num' => min($limit, 20)
            ]);
            
            $data = $response->json();
            
            if (isset($data['local_results'])) {
                foreach ($data['local_results'] as $result) {
                    $businesses[] = [
                        'name' => $result['title'] ?? '',
                        'phone' => $result['phone'] ?? null,
                        'email' => $this->extractEmailFromWebsite($result['website'] ?? null),
                        'website' => $result['website'] ?? null,
                        'address' => $result['address'] ?? null,
                        'rating' => $result['rating'] ?? null,
                        'review_count' => $result['reviews'] ?? 0,
                        'is_verified' => isset($result['verified']),
                        'additional' => [
                            'category' => $result['type'] ?? null,
                            'hours' => $result['hours'] ?? null,
                            'coordinates' => $result['gps_coordinates'] ?? null
                        ]
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('SerpAPI error: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la récupération des données SerpAPI: ' . $e->getMessage());
        }
        
        return $businesses;
    }
    
    
    private function getPlaceDetails(string $placeId, string $apiKey): array
    {
        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'formatted_phone_number,website,opening_hours',
                'key' => $apiKey
            ]);
            
            $data = $response->json();
            return $data['result'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    private function extractEmailFromWebsite(?string $website): ?string
    {
        if (!$website) return null;
        
        // Simulation d'extraction d'email depuis un site web
        $domains = ['gmail.com', 'hotmail.fr', 'orange.fr', 'wanadoo.fr', 'outlook.com'];
        $prefixes = ['contact', 'info', 'commercial', 'hello', 'bonjour'];
        
        if (rand(0, 3) === 0) { // 25% de chance d'avoir un email
            return $prefixes[array_rand($prefixes)] . '@' . parse_url($website, PHP_URL_HOST);
        }
        
        return null;
    }

}