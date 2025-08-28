<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Folder, Info } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campagnes', href: '/campaigns' },
    { title: 'Nouvelle Campagne Vide', href: '/campaigns/create-empty' },
];

const form = useForm({
    name: '',
    activity_type: '',
    city: '',
    description: '',
});

const submit = () => {
    form.post('/campaigns/create-empty');
};
</script>

<template>
    <Head title="Nouvelle Campagne Vide" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="$inertia.visit('/campaigns/create-choice')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Retour
                </Button>
                <div>
                    <h1 class="text-3xl font-bold">Créer une Campagne Vide</h1>
                    <p class="text-muted-foreground">
                        Créez une campagne sans contacts pour les ajouter plus tard
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Folder class="h-5 w-5" />
                            Informations de la campagne
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="name">Nom de la campagne *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Ex: Campagne Pizzerias 2025"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="activity_type">Type d'activité (optionnel)</Label>
                                <Input
                                    id="activity_type"
                                    v-model="form.activity_type"
                                    placeholder="Ex: Pizzeria, Restaurant, Coiffeur..."
                                />
                                <div v-if="form.errors.activity_type" class="text-sm text-red-600">
                                    {{ form.errors.activity_type }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="city">Ville ou zone géographique (optionnel)</Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    placeholder="Ex: Paris, Lyon, France..."
                                />
                                <div v-if="form.errors.city" class="text-sm text-red-600">
                                    {{ form.errors.city }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description (optionnel)</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Description de votre campagne..."
                                    rows="4"
                                />
                                <div v-if="form.errors.description" class="text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <Button type="submit" class="w-full" :disabled="form.processing">
                                <Folder class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Création en cours...' : 'Créer la campagne vide' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Info class="h-5 w-5 text-blue-500" />
                                À propos des campagnes vides
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="font-medium text-blue-900 mb-1">Qu'est-ce qu'une campagne vide ?</div>
                                <div class="text-sm text-blue-700">
                                    Une campagne vide est une structure de campagne créée sans contacts initiaux. 
                                    Elle vous permet d'organiser et de planifier vos actions marketing.
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="font-medium">Après la création, vous pourrez :</div>
                                <ul class="text-sm text-muted-foreground space-y-1">
                                    <li>• Ajouter des contacts manuellement un par un</li>
                                    <li>• Importer des contacts depuis un fichier CSV/Excel</li>
                                    <li>• Lancer un scraping Google Maps</li>
                                    <li>• Copier des contacts depuis une autre campagne</li>
                                    <li>• Organiser vos contacts par listes</li>
                                </ul>
                            </div>

                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="font-medium text-green-900 mb-1">Cas d'usage recommandés</div>
                                <ul class="text-sm text-green-700 space-y-1">
                                    <li>• Préparation d'une campagne future</li>
                                    <li>• Organisation de contacts existants</li>
                                    <li>• Test de templates d'emails</li>
                                    <li>• Segmentation manuelle de contacts</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Actions rapides</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" class="w-full justify-start" @click="$inertia.visit('/campaigns/create-with-import')">
                                <FileSpreadsheet class="mr-2 h-4 w-4" />
                                Créer avec import de fichier
                            </Button>
                            <Button variant="outline" class="w-full justify-start" @click="$inertia.visit('/campaigns/create-scraping')">
                                <Search class="mr-2 h-4 w-4" />
                                Créer avec scraping Google Maps
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Import Textarea component styles if needed */
</style>