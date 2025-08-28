<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Edit, Download, Settings, Sync, RefreshCw, Clock } from 'lucide-vue-next';
import { ref } from 'vue';

interface ContactList {
    id: number;
    name: string;
    description: string;
    status: 'active' | 'inactive';
    total_contacts: number;
    contacts_count: number;
    segments_count: number;
    auto_sync: boolean;
    sync_campaign_id?: number;
    sync_criteria?: any;
    last_synced_at?: string;
    synced_contacts_count: number;
    created_at: string;
}

interface Stats {
    total_lists: number;
    active_lists: number;
    total_contacts_in_lists: number;
}

interface Props {
    lists: {
        data: ContactList[];
        links: any[];
        meta: any;
    };
    stats: Stats;
    filters: any;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Listes de Contacts', href: '/contact-lists' },
];

const syncing = ref<number[]>([]);

const syncList = async (listId: number) => {
    if (syncing.value.includes(listId)) return;
    
    syncing.value.push(listId);
    try {
        await router.post(`/contact-lists/${listId}/sync`);
    } finally {
        syncing.value = syncing.value.filter(id => id !== listId);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getSyncStatusVariant = (list: ContactList) => {
    if (list.last_synced_at) return 'default';
    return 'outline';
};

const getSyncStatusText = (list: ContactList) => {
    if (list.last_synced_at) return 'Synchronisé';
    return 'Non synchronisé';
};
</script>

<template>
    <Head title="Listes de Contacts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Listes de Contacts</h1>
                    <p class="text-muted-foreground">
                        Gérez et synchronisez vos listes de contacts
                    </p>
                </div>
                <Link href="/contact-lists/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Nouvelle Liste
                    </Button>
                </Link>
            </div>

            <!-- Statistiques -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Listes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_lists }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Listes Actives</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.active_lists }}</div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Contacts en Listes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_contacts_in_lists }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filtres -->
            <div class="flex items-center space-x-2">
                <Input
                    placeholder="Rechercher une liste..."
                    class="max-w-sm"
                />
            </div>

            <!-- Listes -->
            <div v-if="lists.data.length === 0" class="text-center py-12">
                <div class="text-muted-foreground">
                    <Plus class="mx-auto h-12 w-12 mb-4" />
                    <h3 class="text-lg font-semibold mb-2">Aucune liste</h3>
                    <p class="mb-4">Créez votre première liste de contacts pour commencer.</p>
                    <Link href="/contact-lists/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Créer une Liste
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="list in lists.data" :key="list.id" class="hover:shadow-md transition-shadow">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="space-y-1 flex-1">
                                <CardTitle class="text-lg">{{ list.name }}</CardTitle>
                                <CardDescription v-if="list.description">
                                    {{ list.description }}
                                </CardDescription>
                            </div>
                            <Badge :variant="list.status === 'active' ? 'default' : 'outline'">
                                {{ list.status === 'active' ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </CardHeader>
                    
                    <CardContent class="space-y-4">
                        <!-- Statistiques de la liste -->
                        <div class="flex justify-between text-sm">
                            <span>Contacts: <strong>{{ list.total_contacts }}</strong></span>
                            <span>Segments: <strong>{{ list.segments_count }}</strong></span>
                        </div>

                        <!-- Statut de synchronisation -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Synchronisation</span>
                                <Badge :variant="getSyncStatusVariant(list)">
                                    {{ getSyncStatusText(list) }}
                                </Badge>
                            </div>
                            
                            <div class="text-xs text-muted-foreground space-y-1">
                                <div v-if="list.last_synced_at" class="flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    Dernière sync: {{ formatDate(list.last_synced_at) }}
                                </div>
                                <div v-else class="text-muted-foreground">
                                    Jamais synchronisé
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col space-y-2">
                            <!-- Actions principales -->
                            <div class="flex space-x-2">
                                <Link :href="`/contact-lists/${list.id}`">
                                    <Button size="sm" variant="outline" class="flex-1">
                                        <Eye class="mr-1 h-3 w-3" />
                                        Voir
                                    </Button>
                                </Link>
                                
                                <Link :href="`/contact-lists/${list.id}/edit`">
                                    <Button size="sm" variant="outline" class="flex-1">
                                        <Edit class="mr-1 h-3 w-3" />
                                        Modifier
                                    </Button>
                                </Link>
                            </div>

                            <!-- Synchronisation -->
                            <div class="flex space-x-2">
                                <Button 
                                    size="sm" 
                                    variant="outline" 
                                    class="flex-1"
                                    @click="syncList(list.id)"
                                    :disabled="syncing.includes(list.id)"
                                >
                                    <RefreshCw :class="['mr-1 h-3 w-3', { 'animate-spin': syncing.includes(list.id) }]" />
                                    {{ syncing.includes(list.id) ? 'Synchronisation...' : 'Synchroniser' }}
                                </Button>
                            </div>

                            <!-- Actions supplémentaires -->
                            <div class="flex space-x-2 pt-2 border-t">
                                <Link :href="`/contact-lists/${list.id}/export`" class="flex-1">
                                    <Button size="sm" variant="ghost" class="w-full">
                                        <Download class="mr-1 h-3 w-3" />
                                        Exporter
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        <div class="text-xs text-muted-foreground pt-2 border-t">
                            Créée le {{ formatDate(list.created_at) }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination -->
            <div v-if="lists.meta && lists.data.length > 0" class="flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Affichage de {{ lists.meta.from }} à {{ lists.meta.to }} sur {{ lists.meta.total }} résultats
                </div>
                
                <div class="flex space-x-2">
                    <Button 
                        v-for="link in lists.meta.links"
                        :key="link.label"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        :disabled="!link.url"
                        @click="link.url && router.get(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>