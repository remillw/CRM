<?php

require_once 'vendor/autoload.php';

// Démarre Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Campaign;
use App\Services\GoogleMapsScrapingService;

echo "=== Test du Scraping Google Maps ===\n\n";

// Créer une campagne de test
$campaign = Campaign::create([
    'name' => 'Test Pizzerias Paris',
    'activity_type' => 'pizzeria',
    'city' => 'Paris',
    'target_count' => 10,
    'status' => 'pending'
]);

echo "✅ Campagne créée : ID {$campaign->id}\n";
echo "📍 Recherche : {$campaign->activity_type} à {$campaign->city}\n";
echo "🎯 Objectif : {$campaign->target_count} contacts\n\n";

// Lancer le scraping
$scrapingService = new GoogleMapsScrapingService();
echo "🔄 Début du scraping...\n";

$scrapingService->scrapeCampaign($campaign);

// Vérifier les résultats
$campaign->refresh();
$contacts = $campaign->contacts;

echo "\n=== Résultats ===\n";
echo "📊 Status : {$campaign->status}\n";
echo "👥 Contacts trouvés : {$contacts->count()}\n";
echo "📧 Avec email : {$contacts->whereNotNull('email')->count()}\n";
echo "🌐 Avec site web : {$contacts->whereNotNull('website')->count()}\n";
echo "⭐ Note moyenne : " . round($contacts->avg('google_rating'), 1) . "/5\n\n";

echo "=== Premiers contacts ===\n";
foreach ($contacts->take(5) as $contact) {
    echo "🏪 {$contact->business_name}\n";
    echo "   📞 {$contact->phone}\n";
    if ($contact->email) echo "   📧 {$contact->email}\n";
    if ($contact->website) echo "   🌐 {$contact->website}\n";
    echo "   📍 {$contact->address}\n";
    echo "   ⭐ {$contact->google_rating}/5 ({$contact->review_count} avis)\n";
    echo "\n";
}

echo "🎉 Test terminé ! Vous pouvez voir les résultats dans l'interface web.\n";
echo "🔗 http://localhost:8000/campaigns/{$campaign->id}\n";