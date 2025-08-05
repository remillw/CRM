<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Prospection - Sans site web',
                'subject' => 'Votre présence en ligne : Une opportunité à saisir pour {{business_name}}',
                'segment_type' => 'no_website',
                'content' => $this->getNoWebsiteTemplate(),
                'variables' => [
                    'business_name' => 'Nom de l\'entreprise',
                    'owner_name' => 'Nom du propriétaire',
                    'activity_type' => 'Type d\'activité',
                    'city' => 'Ville',
                    'tracking_pixel' => 'Pixel de tracking',
                    'unsubscribe_link' => 'Lien de désinscription'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Prospection - Site web non performant',
                'subject' => 'Améliorez la visibilité de {{business_name}} sur Google',
                'segment_type' => 'bad_website',
                'content' => $this->getBadWebsiteTemplate(),
                'variables' => [
                    'business_name' => 'Nom de l\'entreprise',
                    'owner_name' => 'Nom du propriétaire',
                    'website' => 'Site web',
                    'activity_type' => 'Type d\'activité',
                    'city' => 'Ville',
                    'tracking_pixel' => 'Pixel de tracking',
                    'unsubscribe_link' => 'Lien de désinscription'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Relance - Pas de réponse première prospection',
                'subject' => 'Re: Votre présence digitale - {{business_name}}',
                'segment_type' => 'follow_up_1',
                'content' => $this->getFollowUp1Template(),
                'variables' => [
                    'business_name' => 'Nom de l\'entreprise',
                    'owner_name' => 'Nom du propriétaire',
                    'activity_type' => 'Type d\'activité',
                    'city' => 'Ville',
                    'tracking_pixel' => 'Pixel de tracking',
                    'unsubscribe_link' => 'Lien de désinscription'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Relance - Seconde relance',
                'subject' => 'Dernière chance : Développez votre {{activity_type}} à {{city}}',
                'segment_type' => 'follow_up_2',
                'content' => $this->getFollowUp2Template(),
                'variables' => [
                    'business_name' => 'Nom de l\'entreprise',
                    'owner_name' => 'Nom du propriétaire',
                    'activity_type' => 'Type d\'activité',
                    'city' => 'Ville',
                    'tracking_pixel' => 'Pixel de tracking',
                    'unsubscribe_link' => 'Lien de désinscription'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Email ouvert mais pas de réponse',
                'subject' => 'Suite à votre consultation - Offre spéciale {{business_name}}',
                'segment_type' => 'opened_no_response',
                'content' => $this->getOpenedNoResponseTemplate(),
                'variables' => [
                    'business_name' => 'Nom de l\'entreprise',
                    'owner_name' => 'Nom du propriétaire',
                    'activity_type' => 'Type d\'activité',
                    'city' => 'Ville',
                    'tracking_pixel' => 'Pixel de tracking',
                    'unsubscribe_link' => 'Lien de désinscription'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Proposition de partenariat',
                'subject' => 'Partenariat digital pour {{business_name}} - Opportunité exclusive',
                'segment_type' => 'partnership',
                'content' => $this->getPartnershipTemplate(),
                'variables' => [
                    'business_name' => 'Nom de l\'entreprise',
                    'owner_name' => 'Nom du propriétaire',
                    'activity_type' => 'Type d\'activité',
                    'city' => 'Ville',
                    'tracking_pixel' => 'Pixel de tracking',
                    'unsubscribe_link' => 'Lien de désinscription'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }

    private function getNoWebsiteTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre présence en ligne</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
        .cta { background: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 Boostez votre {{activity_type}} à {{city}}</h1>
    </div>
    
    <div class="content">
        <p>Bonjour {{owner_name}},</p>
        
        <p>En recherchant des {{activity_type}} de qualité à {{city}}, j\'ai découvert <strong>{{business_name}}</strong>.</p>
        
        <div class="highlight">
            <strong>⚠️ Problème détecté :</strong> Votre entreprise n\'a pas de site web visible sur Google. 
            Vous perdez probablement des clients qui vous cherchent en ligne.
        </div>
        
        <p><strong>Savez-vous que :</strong></p>
        <ul>
            <li>🔍 75% de vos clients potentiels cherchent un {{activity_type}} sur Google avant d\'appeler</li>
            <li>📱 Sans site web, vous êtes invisible face à vos concurrents</li>
            <li>💰 Chaque jour sans présence en ligne = clients perdus</li>
        </ul>
        
        <p><strong>Solution rapide :</strong></p>
        <p>Je peux créer votre site web professionnel en 48h avec :</p>
        <ul>
            <li>✅ Design moderne et responsive</li>
            <li>✅ Optimisation Google (SEO)</li>
            <li>✅ Formulaire de contact</li>
            <li>✅ Hébergement inclus 1 an</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="mailto:contact@exemple.com?subject=Site web pour {{business_name}}" class="cta">
                💬 Discutons de votre projet (Gratuit)
            </a>
        </div>
        
        <p>Réponse rapide garantie sous 2h.</p>
        
        <p>Cordialement,<br>
        <strong>Votre nom</strong><br>
        Spécialiste en présence digitale</p>
    </div>
    
    <div class="footer">
        <p>{{tracking_pixel}}</p>
        <p><a href="{{unsubscribe_link}}">Se désinscrire</a></p>
    </div>
</body>
</html>';
    }

    private function getBadWebsiteTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Améliorez votre visibilité</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .cta { background: #ff6b6b; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📈 Optimisons {{business_name}}</h1>
    </div>
    
    <div class="content">
        <p>Bonjour {{owner_name}},</p>
        
        <p>J\'ai analysé votre site web <strong>{{website}}</strong> et je vois un potentiel énorme pour votre {{activity_type}} à {{city}}.</p>
        
        <div class="highlight">
            <strong>🔍 Analyse gratuite effectuée :</strong> Votre site a quelques points d\'amélioration qui vous empêchent d\'apparaître en première page Google.
        </div>
        
        <p><strong>Opportunités détectées :</strong></p>
        <ul>
            <li>🎯 Mots-clés mal optimisés pour "{{activity_type}} {{city}}"</li>
            <li>📱 Site non optimisé mobile (60% du trafic perdu)</li>
            <li>⚡ Vitesse de chargement à améliorer</li>
            <li>📞 Manque d\'éléments pour convertir les visiteurs</li>
        </ul>
        
        <p><strong>Résultats attendus après optimisation :</strong></p>
        <ul>
            <li>✅ +300% de visibilité sur Google</li>
            <li>✅ +150% d\'appels depuis votre site</li>
            <li>✅ Devancer vos concurrents locaux</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="mailto:contact@exemple.com?subject=Optimisation SEO pour {{business_name}}" class="cta">
                🚀 Audit SEO gratuit (20 min)
            </a>
        </div>
        
        <p>Je vous envoie un rapport détaillé dans les 24h.</p>
        
        <p>Cordialement,<br>
        <strong>Votre nom</strong><br>
        Expert SEO local</p>
    </div>
    
    <div class="footer">
        <p>{{tracking_pixel}}</p>
        <p><a href="{{unsubscribe_link}}">Se désinscrire</a></p>
    </div>
</body>
</html>';
    }

    private function getFollowUp1Template(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relance</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50; margin: 20px 0; }
        .cta { background: #2196f3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⏰ Dernière chance {{business_name}}</h1>
    </div>
    
    <div class="content">
        <p>Bonjour {{owner_name}},</p>
        
        <p>Il y a quelques jours, je vous ai contacté concernant la présence digitale de <strong>{{business_name}}</strong>.</p>
        
        <div class="highlight">
            <strong>🎯 Peut-être que mon message s\'est perdu ?</strong> Je sais que vous êtes occupé(e) à faire tourner votre {{activity_type}}.
        </div>
        
        <p><strong>Juste un rappel rapide :</strong></p>
        <p>Vos concurrents à {{city}} gagnent des clients chaque jour grâce à leur présence en ligne, pendant que vous passez à côté d\'opportunités.</p>
        
        <p><strong>Ce que mes autres clients {{activity_type}} ont obtenu :</strong></p>
        <ul>
            <li>📞 Sarah (Coiffeuse Lyon) : +40 nouveaux clients/mois</li>
            <li>🍕 Marc (Pizzaiolo Marseille) : +25% de commandes</li>
            <li>🔧 Pierre (Plombier Nice) : Agenda complet 3 mois à l\'avance</li>
        </ul>
        
        <div style="text-align: center;">
            <a href="mailto:contact@exemple.com?subject=Relance - {{business_name}}" class="cta">
                💬 Juste 5 minutes au téléphone ?
            </a>
        </div>
        
        <p><em>PS: Si vous n\'êtes pas intéressé(e), répondez simplement "NON" et je ne vous recontacterai plus.</em></p>
        
        <p>Bonne journée,<br>
        <strong>Votre nom</strong></p>
    </div>
    
    <div class="footer">
        <p>{{tracking_pixel}}</p>
        <p><a href="{{unsubscribe_link}}">Se désinscrire</a></p>
    </div>
</body>
</html>';
    }

    private function getFollowUp2Template(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dernière chance</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #ffe6e6; padding: 15px; border-left: 4px solid #f44336; margin: 20px 0; }
        .cta { background: #e91e63; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 Dernière opportunité</h1>
    </div>
    
    <div class="content">
        <p>Bonjour {{owner_name}},</p>
        
        <p>C\'est la dernière fois que je vous contacte concernant {{business_name}}.</p>
        
        <div class="highlight">
            <strong>⚠️ Réalité du marché :</strong> Pendant que vous lisez cet email, 3 clients potentiels cherchent un {{activity_type}} à {{city}} sur Google... et ne vous trouvent pas.
        </div>
        
        <p><strong>Offre de départ (valable 48h seulement) :</strong></p>
        <ul>
            <li>🎁 Audit SEO complet gratuit (valeur 200€)</li>
            <li>💰 -50% sur la création/optimisation</li>
            <li>🚀 Mise en ligne en 48h chrono</li>
            <li>📈 Garantie de résultats sous 30 jours</li>
        </ul>
        
        <p><strong>Après 48h, cette offre ne sera plus disponible.</strong></p>
        
        <p>Soit vous saisissez cette opportunité maintenant, soit vous continuez à regarder vos concurrents prendre vos clients.</p>
        
        <div style="text-align: center;">
            <a href="mailto:contact@exemple.com?subject=URGENT - Offre 48h {{business_name}}" class="cta">
                🔥 Je saisis l\'offre maintenant
            </a>
        </div>
        
        <p>À dans 2 minutes j\'espère,<br>
        <strong>Votre nom</strong></p>
        
        <p><em>PS: Si vous ne répondez pas, je comprendrai que vous préférez laisser vos concurrents gagner.</em></p>
    </div>
    
    <div class="footer">
        <p>{{tracking_pixel}}</p>
        <p><a href="{{unsubscribe_link}}">Se désinscrire</a></p>
    </div>
</body>
</html>';
    }

    private function getOpenedNoResponseTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suite à votre consultation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0; }
        .cta { background: #ff9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>👀 J\'ai vu que vous aviez consulté mon message</h1>
    </div>
    
    <div class="content">
        <p>Bonjour {{owner_name}},</p>
        
        <p>J\'ai remarqué que vous avez ouvert mon email concernant <strong>{{business_name}}</strong>. Merci pour votre attention !</p>
        
        <div class="highlight">
            <strong>🤔 Vous hésitez peut-être ?</strong> C\'est normal ! Investir dans sa présence digitale est une décision importante.
        </div>
        
        <p><strong>Pour vous aider à décider, voici ce que je propose :</strong></p>
        
        <p>🎁 <strong>Audit GRATUIT et sans engagement</strong> de votre situation actuelle :</p>
        <ul>
            <li>✅ Analyse de votre positionnement Google</li>
            <li>✅ Étude de vos 3 principaux concurrents</li>
            <li>✅ Plan d\'action personnalisé</li>
            <li>✅ Estimation du potentiel de clients supplémentaires</li>
        </ul>
        
        <p><strong>Pourquoi cet audit gratuit ?</strong><br>
        Parce que je sais qu\'une fois que vous verrez le potentiel, vous voudrez travailler avec moi.</p>
        
        <div style="text-align: center;">
            <a href="mailto:contact@exemple.com?subject=Audit gratuit - {{business_name}}" class="cta">
                📊 J\'accepte l\'audit gratuit
            </a>
        </div>
        
        <p>Vous recevrez votre rapport personnalisé sous 24h, même si vous décidez de ne pas aller plus loin.</p>
        
        <p>À bientôt,<br>
        <strong>Votre nom</strong></p>
    </div>
    
    <div class="footer">
        <p>{{tracking_pixel}}</p>
        <p><a href="{{unsubscribe_link}}">Se désinscrire</a></p>
    </div>
</body>
</html>';
    }

    private function getPartnershipTemplate(): string
    {
        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partenariat</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .highlight { background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50; margin: 20px 0; }
        .cta { background: #9c27b0; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🤝 Partenariat exclusif</h1>
        <p>Pour les {{activity_type}} ambitieux de {{city}}</p>
    </div>
    
    <div class="content">
        <p>Bonjour {{owner_name}},</p>
        
        <p>Votre {{activity_type}} <strong>{{business_name}}</strong> a retenu mon attention pour une proposition de partenariat unique.</p>
        
        <div class="highlight">
            <strong>🎯 Concept :</strong> Je vous aide à devenir LE référent {{activity_type}} de {{city}} sur Google, en échange d\'un partenariat long terme.
        </div>
        
        <p><strong>Voici ma proposition WIN-WIN :</strong></p>
        
        <p><strong>Ce que JE fais pour vous :</strong></p>
        <ul>
            <li>🚀 Site web professionnel + SEO premium</li>
            <li>📱 Optimisation mobile et réseaux sociaux</li>
            <li>📈 Stratégie de contenu mensuelle</li>
            <li>🎯 Campagnes Google Ads ciblées</li>
            <li>📊 Reporting mensuel détaillé</li>
        </ul>
        
        <p><strong>Ce que VOUS faites :</strong></p>
        <ul>
            <li>✅ Devenez ambassadeur de mes services</li>
            <li>✅ Témoignage et recommandations</li>
            <li>✅ Études de cas de votre succès</li>
        </ul>
        
        <p><strong>Résultat attendu :</strong> Vous dominez {{city}} dans 6 mois, je gagne un client référent.</p>
        
        <div style="text-align: center;">
            <a href="mailto:contact@exemple.com?subject=Partenariat - {{business_name}}" class="cta">
                🤝 Discutons de ce partenariat
            </a>
        </div>
        
        <p>Cette offre est limitée à 3 partenaires par ville. {{city}} n\'a plus que 2 places disponibles.</p>
        
        <p>Intéressé(e) ?<br>
        <strong>Votre nom</strong><br>
        Partenaire Digital</p>
    </div>
    
    <div class="footer">
        <p>{{tracking_pixel}}</p>
        <p><a href="{{unsubscribe_link}}">Se désinscrire</a></p>
    </div>
</body>
</html>';
    }
}