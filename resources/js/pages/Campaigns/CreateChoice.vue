<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, Search, FileSpreadsheet, Folder, ArrowRight } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campagnes', href: '/campaigns' },
    { title: 'Nouvelle Campagne', href: '/campaigns/create-choice' },
];
</script>

<template>
    <Head title="Nouvelle Campagne - Choix" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="$inertia.visit('/campaigns')">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Retour
                </Button>
                <div>
                    <h1 class="text-3xl font-bold">Créer une Nouvelle Campagne</h1>
                    <p class="text-muted-foreground">
                        Choisissez comment vous souhaitez créer votre campagne
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3 max-w-5xl mx-auto w-full mt-8">
                <!-- Campagne vide -->
                <Card class="cursor-pointer hover:border-blue-500 transition-colors" @click="$inertia.visit('/campaigns/create-empty')">
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Folder class="h-6 w-6 text-blue-600" />
                        </div>
                        <CardTitle>Campagne Vide</CardTitle>
                        <CardDescription>
                            Créer une campagne vide pour ajouter des contacts manuellement plus tard
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul class="text-sm text-muted-foreground space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Création rapide</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Ajout manuel de contacts</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Organisation flexible</span>
                            </li>
                        </ul>
                        <Button class="w-full mt-4" variant="outline">
                            Créer une campagne vide
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                    </CardContent>
                </Card>

                <!-- Import fichier -->
                <Card class="cursor-pointer hover:border-green-500 transition-colors" @click="$inertia.visit('/campaigns/create-with-import')">
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <FileSpreadsheet class="h-6 w-6 text-green-600" />
                        </div>
                        <CardTitle>Import Fichier</CardTitle>
                        <CardDescription>
                            Importer une liste de contacts depuis un fichier CSV ou Excel
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul class="text-sm text-muted-foreground space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Import CSV/Excel</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Traitement par batch</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Jusqu'à 10 000 contacts</span>
                            </li>
                        </ul>
                        <Button class="w-full mt-4" variant="outline">
                            Importer un fichier
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                    </CardContent>
                </Card>

                <!-- Scraping Google Maps -->
                <Card class="cursor-pointer hover:border-purple-500 transition-colors" @click="$inertia.visit('/campaigns/create-scraping')">
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <Search class="h-6 w-6 text-purple-600" />
                        </div>
                        <CardTitle>Scraping Google Maps</CardTitle>
                        <CardDescription>
                            Rechercher et extraire automatiquement des contacts depuis Google Maps
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul class="text-sm text-muted-foreground space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Recherche automatique</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Données complètes</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-500 mt-0.5">✓</span>
                                <span>Notes et avis Google</span>
                            </li>
                        </ul>
                        <Button class="w-full mt-4" variant="outline">
                            Lancer le scraping
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div class="max-w-3xl mx-auto mt-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Besoin d'aide pour choisir ?</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <div class="font-semibold text-sm mb-1">Campagne Vide</div>
                                <p class="text-sm text-muted-foreground">
                                    Idéal si vous voulez créer une structure de campagne et ajouter les contacts progressivement.
                                </p>
                            </div>
                            <div>
                                <div class="font-semibold text-sm mb-1">Import Fichier</div>
                                <p class="text-sm text-muted-foreground">
                                    Parfait si vous avez déjà une liste de contacts dans un tableur ou un fichier CSV.
                                </p>
                            </div>
                            <div>
                                <div class="font-semibold text-sm mb-1">Scraping Google Maps</div>
                                <p class="text-sm text-muted-foreground">
                                    Recommandé pour découvrir de nouveaux contacts dans une zone géographique spécifique.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>