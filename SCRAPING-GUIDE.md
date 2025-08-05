# Guide d'utilisation de l'outil Web Scraping

## 🚀 Fonctionnalités

Votre CRM dispose maintenant d'un outil de web scraping gratuit et puissant qui permet d'extraire :

- **Données générales** : titre, description, métadonnées
- **Emails** : tous les emails présents sur une page
- **Liens** : liens internes et externes avec leur texte
- **Images** : toutes les images avec leurs attributs alt
- **Tableaux** : extraction complète des tableaux HTML

## 🔧 Comment utiliser l'outil

### 1. Interface Web
Accédez à `http://localhost:8000/web-scraping` depuis votre navigateur.

### 2. Utilisation programmatique

```php
use App\Services\WebScrapingService;

$scraper = new WebScrapingService();

// Scraping général
$result = $scraper->scrapeUrl('https://example.com');

// Extraction d'emails
$emails = $scraper->extractEmails('https://example.com');

// Extraction de liens
$links = $scraper->extractLinks('https://example.com');

// Extraction d'images
$images = $scraper->extractImages('https://example.com');

// Extraction de tableaux
$tables = $scraper->extractTable('https://example.com');
```

### 3. API REST

```bash
# Scraping général
curl -X POST http://localhost:8000/api/web-scraping/scrape \
  -H "Content-Type: application/json" \
  -d '{"url": "https://example.com"}'

# Extraction d'emails
curl -X POST http://localhost:8000/api/web-scraping/extract-emails \
  -H "Content-Type: application/json" \
  -d '{"url": "https://example.com"}'
```

## 📊 Exemples d'utilisation

### Scraper une liste de prospects
```php
$urls = [
    'https://company1.com/contact',
    'https://company2.com/about',
    'https://company3.com/team'
];

$results = $scraper->scrapeMultipleUrls($urls, [], ['delay' => 2]);

foreach ($results as $result) {
    if (isset($result['emails'])) {
        // Ajouter les emails à votre CRM
        foreach ($result['emails'] as $email) {
            // Code pour sauvegarder l'email
        }
    }
}
```

### Extraire des données de tableau
```php
$tableData = $scraper->extractTable('https://example.com/pricing');

foreach ($tableData['tables'] as $table) {
    foreach ($table['rows'] as $row) {
        // Traiter chaque ligne du tableau
        print_r($row);
    }
}
```

## ⚙️ Configuration avancée

### Options disponibles
- `delay` : délai entre les requêtes (en secondes)
- `include_html` : inclure le HTML brut dans la réponse
- `headers` : headers HTTP personnalisés

### Sélecteurs CSS personnalisés
```php
$selectors = [
    'title' => 'h1.main-title',
    'price' => '.price-value',
    'description' => '.product-description',
    'images' => [
        'selector' => 'img.product-image',
        'attribute' => 'src'
    ]
];

$result = $scraper->scrapeUrl('https://example.com', $selectors);
```

## 🛡️ Bonnes pratiques

1. **Respectez les robots.txt** des sites web
2. **Ajoutez des délais** entre les requêtes pour éviter la surcharge
3. **Gérez les erreurs** appropriément
4. **Respectez les conditions d'utilisation** des sites
5. **Ne scrapez que les données publiques**

## 🔍 Dépannage

### Erreurs communes
- **Timeout** : augmentez le délai dans les options
- **403 Forbidden** : le site bloque les robots, essayez avec des headers différents
- **Empty results** : vérifiez que les sélecteurs CSS sont corrects

### Test de l'installation
```bash
php test-scraping.php
```

## 📈 Intégration avec votre CRM

L'outil est parfaitement intégré à votre CRM Laravel :
- Accessible via le menu latéral "Web Scraping"
- API REST disponible pour l'automatisation
- Service PHP réutilisable dans vos contrôleurs
- Interface Vue.js moderne et responsive

Vous pouvez maintenant extraire gratuitement des données de n'importe quel site web public !