<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactListItem;
use Illuminate\Database\Seeder;

class TestContactListSeeder extends Seeder
{
    public function run(): void
    {
        // Créer une campagne de test "Pizzeria Test"
        $testCampaign = Campaign::updateOrCreate(
            ['name' => 'Pizzeria Test'],
            [
                'activity_type' => 'pizzeria',
                'city' => 'Lyon',
                'target_count' => 2,
                'status' => 'running',
                'config' => [
                    'test_campaign' => true,
                    'description' => 'Campagne de test pour les emails'
                ],
                'started_at' => now(),
            ]
        );

        // Créer les contacts de test
        $contacts = [
            [
                'business_name' => 'Pizza Del Sol',
                'owner_name' => 'Rémi Bouvant',
                'email' => 'r.bouvant@gmail.com',
                'phone' => '04 12 34 56 78',
                'address' => '123 Rue de la Paix',
                'city' => 'Lyon',
                'postal_code' => '69000',
                'website' => 'https://pizzadelsol-test.fr',
                'activity_type' => 'pizzeria',
                'site_good' => true,
                'can_command' => true,
                'notes' => 'Contact de test - Propriétaire principal'
            ],
            [
                'business_name' => 'Mama Mia Pizzeria',
                'owner_name' => 'Manon Mesnil',
                'email' => 'manoon.mesnil@gmail.com',
                'phone' => '04 87 65 43 21',
                'address' => '456 Avenue des Alpes',
                'city' => 'Lyon',
                'postal_code' => '69001',
                'website' => 'https://mamamia-pizzeria-test.fr',
                'activity_type' => 'pizzeria',
                'site_good' => false,
                'can_command' => true,
                'notes' => 'Contact de test - Site web à améliorer'
            ]
        ];

        $createdContacts = [];
        foreach ($contacts as $contactData) {
            $contact = Contact::updateOrCreate(
                ['email' => $contactData['email']],
                array_merge($contactData, ['campaign_id' => $testCampaign->id])
            );
            $createdContacts[] = $contact;
        }

        // Créer plusieurs listes de contacts pour les tests
        $contactLists = [
            [
                'name' => '🧪 Liste de Test - Emails',
                'description' => 'Liste de test avec r.bouvant@gmail.com et manoon.mesnil@gmail.com pour tester les envois d\'emails',
                'contacts' => $createdContacts, // Tous les contacts
            ],
            [
                'name' => '🍕 Pizzerias - Sites à améliorer',
                'description' => 'Pizzerias avec des sites web non performants (site_good = false)',
                'contacts' => collect($createdContacts)->where('site_good', false)->values()->all(),
            ],
            [
                'name' => '🍕 Pizzerias - Peuvent commander',
                'description' => 'Pizzerias qui peuvent passer commande (can_command = true)',
                'contacts' => collect($createdContacts)->where('can_command', true)->values()->all(),
            ],
            [
                'name' => '🏆 Pizzerias - Sites performants',
                'description' => 'Pizzerias avec de bons sites web (site_good = true)',
                'contacts' => collect($createdContacts)->where('site_good', true)->values()->all(),
            ]
        ];

        foreach ($contactLists as $listData) {
            $contactList = ContactList::updateOrCreate(
                ['name' => $listData['name']],
                [
                    'description' => $listData['description'],
                ]
            );

            // Vider les contacts existants de la liste
            ContactListItem::where('list_id', $contactList->id)->delete();

            // Ajouter les contacts à la liste
            foreach ($listData['contacts'] as $contact) {
                ContactListItem::create([
                    'list_id' => $contactList->id,
                    'contact_id' => $contact->id,
                ]);
            }

            echo "✅ Liste créée: {$contactList->name} avec " . count($listData['contacts']) . " contacts\n";
        }

        echo "\n🎯 Campagne de test créée: {$testCampaign->name}\n";
        echo "📧 Emails de test disponibles:\n";
        echo "   - r.bouvant@gmail.com (Pizza Del Sol)\n";
        echo "   - manoon.mesnil@gmail.com (Mama Mia Pizzeria)\n";
        echo "\n📋 4 listes de contacts créées pour les tests d'envoi\n";
    }
}