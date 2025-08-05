<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, MapPin, Search, Target, Hash } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campagnes', href: '/campaigns' },
    { title: 'Nouvelle Campagne', href: '/campaigns/create' },
];

const form = useForm({
    name: '',
    activity_type: '',
    city: '',
    target_count: 50,
});

const submit = () => {
    form.post('/campaigns');
};

const suggestedActivities = [
    'Pizzeria', 'Restaurant', 'Coiffeur', 'Boulangerie', 'Pharmacie',
    'Garage', 'Dentiste', 'Avocat', 'Plombier', 'Électricien'
];

const suggestedCities = [
    'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice',
    'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille'
];

const selectActivity = (activity: string) => {
    form.activity_type = activity;
    if (!form.name) {
        form.name = `Campagne ${activity} ${form.city || new Date().getFullYear()}`;
    }
};

const selectCity = (city: string) => {
    form.city = city;
    if (form.activity_type && !form.name) {
        form.name = `Campagne ${form.activity_type} ${city}`;
    }
};
</script>

<template>
    <Head title="Nouvelle Campagne" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="$inertia.visit('/campaigns')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Retour
                </Button>
                <div>
                    <h1 class="text-3xl font-bold">Nouvelle Campagne</h1>
                    <p class="text-muted-foreground">
                        Configurez votre campagne de scraping Google Maps
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Configuration</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-2">
                                <Label for="name">Nom de la campagne</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Ex: Pizzerias Paris 2024"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="activity_type" class="flex items-center gap-2">
                                    <Target class="h-4 w-4" />
                                    Type d'activité
                                </Label>
                                <Input
                                    id="activity_type"
                                    v-model="form.activity_type"
                                    placeholder="Ex: Pizzeria, Restaurant, Coiffeur..."
                                    required
                                />
                                <div v-if="form.errors.activity_type" class="text-sm text-red-600">
                                    {{ form.errors.activity_type }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="city" class="flex items-center gap-2">
                                    <MapPin class="h-4 w-4" />
                                    Ville
                                </Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    placeholder="Ex: Paris, Lyon, Marseille..."
                                    required
                                />
                                <div v-if="form.errors.city" class="text-sm text-red-600">
                                    {{ form.errors.city }}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="target_count" class="flex items-center gap-2">
                                    <Hash class="h-4 w-4" />
                                    Nombre de contacts souhaité
                                </Label>
                                <Input
                                    id="target_count"
                                    v-model.number="form.target_count"
                                    type="number"
                                    min="1"
                                    max="1000"
                                    required
                                />
                                <div class="text-sm text-muted-foreground">
                                    Entre 1 et 1000 contacts
                                </div>
                                <div v-if="form.errors.target_count" class="text-sm text-red-600">
                                    {{ form.errors.target_count }}
                                </div>
                            </div>

                            <Button type="submit" class="w-full" :disabled="form.processing">
                                <Search class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Création en cours...' : 'Lancer le scraping' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Activités suggérées</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-2 grid-cols-2">
                                <Button
                                    v-for="activity in suggestedActivities"
                                    :key="activity"
                                    variant="outline"
                                    size="sm"
                                    @click="selectActivity(activity)"
                                    :class="{ 'bg-blue-50 border-blue-200': form.activity_type === activity }"
                                >
                                    {{ activity }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Villes suggérées</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-2 grid-cols-2">
                                <Button
                                    v-for="city in suggestedCities"
                                    :key="city"
                                    variant="outline"
                                    size="sm"
                                    @click="selectCity(city)"
                                    :class="{ 'bg-blue-50 border-blue-200': form.city === city }"
                                >
                                    {{ city }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Informations</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4 text-sm text-muted-foreground">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="font-medium text-blue-900 mb-1">Comment ça marche ?</div>
                                <div class="text-blue-700">
                                    Notre système va rechercher des entreprises correspondant à votre activité dans la ville choisie et extraire leurs informations de contact.
                                </div>
                            </div>
                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="font-medium text-green-900 mb-1">Données récupérées</div>
                                <div class="text-green-700">
                                    • Nom de l'entreprise<br>
                                    • Téléphone<br>
                                    • Email (si disponible)<br>
                                    • Site web (si disponible)<br>
                                    • Adresse<br>
                                    • Note Google et nombre d'avis
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>