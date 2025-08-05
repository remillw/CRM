<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Trash2, RotateCcw, MapPin, Target } from 'lucide-vue-next';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

interface Campaign {
    id: number;
    name: string;
    activity_type: string;
    city: string;
    target_count: number;
    status: 'pending' | 'running' | 'completed' | 'failed';
    contacts_count: number;
    created_at: string;
}

interface Props {
    campaigns: {
        data: Campaign[];
        links: any[];
        meta: any;
    };
    filters: any;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campagnes', href: '/campaigns' },
];

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed': return 'default';
        case 'running': return 'secondary';
        case 'failed': return 'destructive';
        default: return 'outline';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'completed': return 'Terminée';
        case 'running': return 'En cours';
        case 'failed': return 'Échouée';
        case 'pending': return 'En attente';
        default: return status;
    }
};

const deleteCampaign = (id: number) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette campagne ?')) {
        router.delete(`/campaigns/${id}`);
    }
};

const restartCampaign = (id: number) => {
    router.post(`/campaigns/${id}/restart`);
};
</script>

<template>
    <Head title="Campagnes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Campagnes de Scraping</h1>
                    <p class="text-muted-foreground">
                        Gérez vos campagnes de scraping Google Maps
                    </p>
                </div>
                <Link href="/campaigns/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Nouvelle Campagne
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6">
                <div v-for="campaign in campaigns.data" :key="campaign.id">
                    <Card>
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-3">
                                        <CardTitle class="text-xl">{{ campaign.name }}</CardTitle>
                                        <Badge :variant="getStatusVariant(campaign.status)">
                                            {{ getStatusText(campaign.status) }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                        <div class="flex items-center gap-1">
                                            <Target class="h-4 w-4" />
                                            {{ campaign.activity_type }}
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <MapPin class="h-4 w-4" />
                                            {{ campaign.city }}
                                        </div>
                                        <div>
                                            Créée le {{ format(new Date(campaign.created_at), 'dd MMM yyyy', { locale: fr }) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Link :href="`/campaigns/${campaign.id}`">
                                        <Button variant="outline" size="sm">
                                            <Eye class="mr-2 h-4 w-4" />
                                            Voir
                                        </Button>
                                    </Link>
                                    <Button 
                                        v-if="campaign.status === 'failed'"
                                        variant="outline" 
                                        size="sm"
                                        @click="restartCampaign(campaign.id)"
                                    >
                                        <RotateCcw class="mr-2 h-4 w-4" />
                                        Relancer
                                    </Button>
                                    <Button 
                                        variant="outline" 
                                        size="sm"
                                        @click="deleteCampaign(campaign.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Supprimer
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <div class="text-sm font-medium">Progression</div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-blue-600 h-2 rounded-full transition-all"
                                                :style="{ width: `${Math.min((campaign.contacts_count / campaign.target_count) * 100, 100)}%` }"
                                            ></div>
                                        </div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ campaign.contacts_count }} / {{ campaign.target_count }}
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-sm font-medium">Contacts trouvés</div>
                                    <div class="text-2xl font-bold">{{ campaign.contacts_count }}</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="campaigns.data.length === 0" class="text-center py-12">
                    <div class="text-muted-foreground mb-4">Aucune campagne créée</div>
                    <Link href="/campaigns/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Créer votre première campagne
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>