<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, FileSpreadsheet, Upload, Download, Users, AlertCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campagnes', href: '/campaigns' },
    { title: 'Import Contacts', href: '/campaigns/create-with-import' },
];

const form = useForm({
    name: '',
    description: '',
    file: null as File | null,
    contacts: [] as any[]
});

const fileInput = ref<HTMLInputElement>();
const previewData = ref<any[]>([]);
const fileError = ref('');

const handleFileSelect = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    
    if (!file) return;
    
    fileError.value = '';
    
    // Validate file type
    const allowedTypes = [
        'text/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    if (!allowedTypes.includes(file.type)) {
        fileError.value = 'Format de fichier non supporté. Utilisez CSV ou Excel.';
        input.value = '';
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        fileError.value = 'Le fichier est trop volumineux (max 5MB).';
        input.value = '';
        return;
    }
    
    form.file = file;
    
    // Generate campaign name from filename if empty
    if (!form.name) {
        const fileName = file.name.replace(/\.[^/.]+$/, '');
        form.name = `Campagne ${fileName} ${new Date().getFullYear()}`;
    }
    
    // Preview file content (we'll parse it on backend)
    await previewFile(file);
};

const previewFile = async (file: File) => {
    if (file.type === 'text/csv') {
        const text = await file.text();
        const lines = text.split('\n').slice(0, 6); // Preview first 5 rows + header
        const headers = lines[0].split(',').map(h => h.trim());
        
        previewData.value = lines.slice(1).map(line => {
            const values = line.split(',');
            return headers.reduce((obj, header, index) => {
                obj[header] = values[index]?.trim() || '';
                return obj;
            }, {} as any);
        }).filter(row => Object.values(row).some(v => v));
    }
};

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('description', form.description);
    if (form.file) {
        formData.append('file', form.file);
    }
    
    form.post('/campaigns/import', {
        forceFormData: true,
        onSuccess: () => {
            // Success handled by redirect
        },
        onError: (errors) => {
            if (errors.file) {
                fileError.value = errors.file;
            }
        }
    });
};

const downloadTemplate = () => {
    window.location.href = '/campaigns/download-template';
};

const hasPreviewData = computed(() => previewData.value.length > 0);
</script>

<template>
    <Head title="Import Contacts Campagne" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="$inertia.visit('/campaigns')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Retour
                </Button>
                <div>
                    <h1 class="text-3xl font-bold">Import de Contacts</h1>
                    <p class="text-muted-foreground">
                        Créez une campagne en important une liste de contacts CSV ou Excel
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Informations de la campagne</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="space-y-2">
                                    <Label for="name">Nom de la campagne</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="Ex: Pizzerias Paris 2025"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">Description (optionnel)</Label>
                                    <Input
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Description de votre campagne..."
                                    />
                                    <div v-if="form.errors.description" class="text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="file" class="flex items-center gap-2">
                                        <FileSpreadsheet class="h-4 w-4" />
                                        Fichier de contacts
                                    </Label>
                                    <div class="space-y-3">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            id="file"
                                            accept=".csv,.xlsx,.xls"
                                            @change="handleFileSelect"
                                            class="hidden"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="w-full"
                                            @click="fileInput?.click()"
                                        >
                                            <Upload class="mr-2 h-4 w-4" />
                                            {{ form.file ? form.file.name : 'Sélectionner un fichier CSV ou Excel' }}
                                        </Button>
                                        <div v-if="fileError" class="text-sm text-red-600">
                                            {{ fileError }}
                                        </div>
                                        <div v-if="form.errors.file" class="text-sm text-red-600">
                                            {{ form.errors.file }}
                                        </div>
                                    </div>
                                </div>

                                <Button 
                                    type="submit" 
                                    class="w-full" 
                                    :disabled="form.processing || !form.file"
                                >
                                    <Users class="mr-2 h-4 w-4" />
                                    {{ form.processing ? 'Import en cours...' : 'Importer les contacts' }}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <Card v-if="hasPreviewData">
                        <CardHeader>
                            <CardTitle>Aperçu du fichier</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b">
                                        <tr>
                                            <th 
                                                v-for="(value, key) in previewData[0]" 
                                                :key="key"
                                                class="text-left p-2 font-medium"
                                            >
                                                {{ key }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr 
                                            v-for="(row, index) in previewData"
                                            :key="index"
                                            class="border-b"
                                        >
                                            <td 
                                                v-for="(value, key) in row"
                                                :key="key"
                                                class="p-2"
                                            >
                                                {{ value || '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-sm text-muted-foreground mt-3">
                                Aperçu des 5 premières lignes
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Modèle de fichier</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <p class="text-sm text-muted-foreground">
                                Téléchargez notre modèle pour formater correctement vos données.
                            </p>
                            <Button 
                                variant="outline" 
                                class="w-full"
                                @click="downloadTemplate"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                Télécharger le modèle
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Format requis</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="font-medium text-blue-900 mb-2 flex items-center gap-2">
                                    <AlertCircle class="h-4 w-4" />
                                    Colonnes obligatoires
                                </div>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• <strong>name</strong> : Nom du contact/entreprise</li>
                                    <li>• <strong>phone</strong> : Numéro de téléphone</li>
                                </ul>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="font-medium text-sm">Colonnes optionnelles :</div>
                                <ul class="text-sm text-muted-foreground space-y-1">
                                    <li>• <strong>email</strong> : Adresse email</li>
                                    <li>• <strong>website</strong> : Site web</li>
                                    <li>• <strong>address</strong> : Adresse complète</li>
                                    <li>• <strong>city</strong> : Ville</li>
                                    <li>• <strong>postal_code</strong> : Code postal</li>
                                    <li>• <strong>rating</strong> : Note Google</li>
                                    <li>• <strong>reviews_count</strong> : Nombre d'avis</li>
                                    <li>• <strong>business_type</strong> : Type d'activité</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Formats supportés</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <FileSpreadsheet class="h-4 w-4 text-green-600" />
                                <span>CSV (.csv)</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <FileSpreadsheet class="h-4 w-4 text-green-600" />
                                <span>Excel (.xlsx, .xls)</span>
                            </div>
                            <div class="text-sm text-muted-foreground mt-3">
                                Taille maximale : 5 MB<br>
                                Limite : 10 000 contacts par fichier
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>