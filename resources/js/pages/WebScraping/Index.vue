<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { 
    Download, 
    Globe, 
    Mail, 
    ExternalLink, 
    Image, 
    Table, 
    Loader2,
    Copy,
    Check
} from 'lucide-vue-next';

const form = reactive({
    url: '',
    selectors: {},
    options: {
        delay: 1,
        include_html: false
    }
});

const results = ref<any>(null);
const loading = ref(false);
const copied = ref(false);

const scrapeUrl = async () => {
    if (!form.url) return;
    
    loading.value = true;
    try {
        const response = await fetch('/api/web-scraping/scrape', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(form)
        });
        
        results.value = await response.json();
    } catch (error) {
        console.error('Erreur de scraping:', error);
    } finally {
        loading.value = false;
    }
};

const extractEmails = async () => {
    if (!form.url) return;
    
    loading.value = true;
    try {
        const response = await fetch('/api/web-scraping/extract-emails', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ url: form.url })
        });
        
        results.value = await response.json();
    } catch (error) {
        console.error('Erreur d\'extraction des emails:', error);
    } finally {
        loading.value = false;
    }
};

const extractLinks = async (externalOnly = false) => {
    if (!form.url) return;
    
    loading.value = true;
    try {
        const response = await fetch('/api/web-scraping/extract-links', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ 
                url: form.url,
                external_only: externalOnly
            })
        });
        
        results.value = await response.json();
    } catch (error) {
        console.error('Erreur d\'extraction des liens:', error);
    } finally {
        loading.value = false;
    }
};

const extractImages = async () => {
    if (!form.url) return;
    
    loading.value = true;
    try {
        const response = await fetch('/api/web-scraping/extract-images', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ url: form.url })
        });
        
        results.value = await response.json();
    } catch (error) {
        console.error('Erreur d\'extraction des images:', error);
    } finally {
        loading.value = false;
    }
};

const extractTable = async () => {
    if (!form.url) return;
    
    loading.value = true;
    try {
        const response = await fetch('/api/web-scraping/extract-table', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ url: form.url })
        });
        
        results.value = await response.json();
    } catch (error) {
        console.error('Erreur d\'extraction de tableau:', error);
    } finally {
        loading.value = false;
    }
};

const copyToClipboard = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    } catch (error) {
        console.error('Erreur de copie:', error);
    }
};

const downloadResults = () => {
    if (!results.value) return;
    
    const blob = new Blob([JSON.stringify(results.value, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `scraping-results-${Date.now()}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};
</script>

<template>
    <Head title="Web Scraping" />

    <div class="container mx-auto py-6">
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold">Outil de Web Scraping</h1>
                <p class="text-muted-foreground">
                    Extrayez des données de n'importe quel site web gratuitement
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Formulaire -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Globe class="w-5 h-5" />
                            Configuration du Scraping
                        </CardTitle>
                        <CardDescription>
                            Saisissez l'URL et choisissez le type d'extraction
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="url">URL à scraper</Label>
                            <Input
                                id="url"
                                v-model="form.url"
                                placeholder="https://example.com"
                                type="url"
                            />
                        </div>

                        <Separator />

                        <div class="grid grid-cols-2 gap-2">
                            <Button 
                                @click="scrapeUrl" 
                                :disabled="loading || !form.url"
                                class="w-full"
                            >
                                <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                                <Globe v-else class="w-4 h-4 mr-2" />
                                Scraping Général
                            </Button>

                            <Button 
                                @click="extractEmails" 
                                :disabled="loading || !form.url"
                                variant="outline"
                                class="w-full"
                            >
                                <Mail class="w-4 h-4 mr-2" />
                                Emails
                            </Button>

                            <Button 
                                @click="extractLinks(false)" 
                                :disabled="loading || !form.url"
                                variant="outline"
                                class="w-full"
                            >
                                <ExternalLink class="w-4 h-4 mr-2" />
                                Liens
                            </Button>

                            <Button 
                                @click="extractImages" 
                                :disabled="loading || !form.url"
                                variant="outline"
                                class="w-full"
                            >
                                <Image class="w-4 h-4 mr-2" />
                                Images
                            </Button>

                            <Button 
                                @click="extractTable" 
                                :disabled="loading || !form.url"
                                variant="outline"
                                class="w-full col-span-2"
                            >
                                <Table class="w-4 h-4 mr-2" />
                                Tableaux
                            </Button>
                        </div>

                        <div class="space-y-2" v-if="form.url">
                            <Label>Options avancées</Label>
                            <div class="flex items-center space-x-2">
                                <Label for="delay" class="text-sm">Délai (secondes):</Label>
                                <Input
                                    id="delay"
                                    v-model.number="form.options.delay"
                                    type="number"
                                    min="0"
                                    max="10"
                                    class="w-20"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Résultats -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Résultats</CardTitle>
                            <CardDescription>
                                Données extraites du site web
                            </CardDescription>
                        </div>
                        <div class="flex gap-2" v-if="results">
                            <Button
                                @click="copyToClipboard(JSON.stringify(results, null, 2))"
                                variant="outline"
                                size="sm"
                            >
                                <Check v-if="copied" class="w-4 h-4" />
                                <Copy v-else class="w-4 h-4" />
                            </Button>
                            <Button
                                @click="downloadResults"
                                variant="outline"
                                size="sm"
                            >
                                <Download class="w-4 h-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="max-h-[500px] overflow-auto">
                            <div v-if="loading" class="flex items-center justify-center h-32">
                                <Loader2 class="w-8 h-8 animate-spin" />
                                <span class="ml-2">Scraping en cours...</span>
                            </div>

                            <div v-else-if="results" class="space-y-4">
                                <!-- Erreur -->
                                <div v-if="results.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <h3 class="font-semibold text-red-800">Erreur</h3>
                                    <p class="text-red-600">{{ results.error }}</p>
                                </div>

                                <!-- Informations générales -->
                                <div v-else class="space-y-6">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label class="text-sm font-medium">URL</Label>
                                            <p class="text-sm text-muted-foreground break-all">{{ results.url }}</p>
                                        </div>
                                        <div>
                                            <Label class="text-sm font-medium">Status</Label>
                                            <Badge :variant="results.status_code === 200 ? 'default' : 'destructive'">
                                                {{ results.status_code }}
                                            </Badge>
                                        </div>
                                    </div>

                                    <!-- Informations générales -->
                                    <div v-if="results.title || results.meta_description || (results.data && Object.keys(results.data).length > 0)" class="space-y-4">
                                        <h3 class="text-lg font-semibold">Informations générales</h3>
                                        <div v-if="results.title">
                                            <Label class="text-sm font-medium">Titre</Label>
                                            <p class="text-sm">{{ results.title }}</p>
                                        </div>
                                        <div v-if="results.meta_description">
                                            <Label class="text-sm font-medium">Description</Label>
                                            <p class="text-sm">{{ results.meta_description }}</p>
                                        </div>
                                        <div v-if="results.data && Object.keys(results.data).length > 0">
                                            <Label class="text-sm font-medium">Données personnalisées</Label>
                                            <pre class="text-xs bg-muted p-2 rounded overflow-auto">{{ JSON.stringify(results.data, null, 2) }}</pre>
                                        </div>
                                    </div>

                                    <!-- Emails -->
                                    <div v-if="results.emails && results.emails.length > 0" class="space-y-2">
                                        <h3 class="text-lg font-semibold">Emails trouvés ({{ results.count }})</h3>
                                        <div class="space-y-1">
                                            <div 
                                                v-for="email in results.emails" 
                                                :key="email"
                                                class="flex items-center justify-between p-2 bg-muted rounded"
                                            >
                                                <span class="text-sm">{{ email }}</span>
                                                <Button
                                                    @click="copyToClipboard(email)"
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    <Copy class="w-3 h-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Liens -->
                                    <div v-if="results.links && results.links.length > 0" class="space-y-2">
                                        <h3 class="text-lg font-semibold">Liens trouvés ({{ results.count }})</h3>
                                        <div class="space-y-1">
                                            <div 
                                                v-for="link in results.links.slice(0, 20)" 
                                                :key="link.url"
                                                class="p-2 bg-muted rounded"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <a 
                                                        :href="link.url" 
                                                        target="_blank"
                                                        class="text-sm font-medium text-blue-600 hover:underline"
                                                    >
                                                        {{ link.text || 'Sans texte' }}
                                                    </a>
                                                    <Badge v-if="link.is_external" variant="outline" class="text-xs">
                                                        Externe
                                                    </Badge>
                                                </div>
                                                <p class="text-xs text-muted-foreground break-all">{{ link.url }}</p>
                                            </div>
                                        </div>
                                        <p v-if="results.links.length > 20" class="text-xs text-muted-foreground">
                                            ... et {{ results.links.length - 20 }} autres liens
                                        </p>
                                    </div>

                                    <!-- Images -->
                                    <div v-if="results.images && results.images.length > 0" class="space-y-2">
                                        <h3 class="text-lg font-semibold">Images trouvées ({{ results.count }})</h3>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div 
                                                v-for="image in results.images.slice(0, 8)" 
                                                :key="image.src"
                                                class="p-2 bg-muted rounded"
                                            >
                                                <img 
                                                    :src="image.src" 
                                                    :alt="image.alt"
                                                    class="w-full h-20 object-cover rounded mb-2"
                                                    loading="lazy"
                                                >
                                                <p class="text-xs break-all">{{ image.alt || 'Sans alt' }}</p>
                                            </div>
                                        </div>
                                        <p v-if="results.images.length > 8" class="text-xs text-muted-foreground">
                                            ... et {{ results.images.length - 8 }} autres images
                                        </p>
                                    </div>

                                    <!-- Tableaux -->
                                    <div v-if="results.tables && results.tables.length > 0" class="space-y-4">
                                        <h3 class="text-lg font-semibold">Tableaux trouvés ({{ results.count }})</h3>
                                        <div 
                                            v-for="(table, index) in results.tables" 
                                            :key="index"
                                            class="border rounded-lg overflow-hidden"
                                        >
                                            <div class="p-2 bg-muted">
                                                <h4 class="text-sm font-medium">Tableau {{ index + 1 }} ({{ table.count }} lignes)</h4>
                                            </div>
                                            <div class="overflow-auto max-h-60">
                                                <table class="w-full text-sm">
                                                    <thead v-if="table.headers.length > 0">
                                                        <tr class="border-b">
                                                            <th 
                                                                v-for="header in table.headers" 
                                                                :key="header"
                                                                class="p-2 text-left font-medium"
                                                            >
                                                                {{ header }}
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr 
                                                            v-for="(row, rowIndex) in table.rows.slice(0, 10)" 
                                                            :key="rowIndex"
                                                            class="border-b"
                                                        >
                                                            <td 
                                                                v-for="(cell, cellKey) in row" 
                                                                :key="cellKey"
                                                                class="p-2"
                                                            >
                                                                {{ cell }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center text-muted-foreground py-8">
                                Saisissez une URL et choisissez une méthode d'extraction pour commencer
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>