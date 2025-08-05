<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    ArrowLeft, 
    RotateCcw, 
    Trash2, 
    Users, 
    Mail, 
    Globe, 
    MapPin, 
    Target,
    Calendar,
    TrendingUp,
    Download
} from 'lucide-vue-next';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

interface Contact {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    website?: string;
    address?: string;
    created_at: string;
}

interface Campaign {
    id: number;
    name: string;
    activity_type: string;
    city: string;
    target_count: number;
    status: 'pending' | 'running' | 'completed' | 'failed';
    created_at: string;
    started_at?: string;
    completed_at?: string;
}

interface Stats {
    total_contacts: number;
    with_email: number;
    with_website: number;
    without_website: number;
    progress: number;
}

interface Props {
    campaign: Campaign;
    stats: Stats;
    recent_contacts: Contact[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Campagnes', href: '/campaigns' },
    { title: props.campaign.name, href: `/campaigns/${props.campaign.id}` },
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

const deleteCampaign = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette campagne ?')) {
        router.delete(`/campaigns/${props.campaign.id}`);
    }
};

const restartCampaign = () => {
    router.post(`/campaigns/${props.campaign.id}/restart`);
};

const exportContacts = () => {
    window.open(`/campaigns/${props.campaign.id}/export`, '_blank');
};
</script>

<template>
    <Head :title="campaign.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <Link href="/campaigns">
                            <Button variant="outline" size="sm">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Retour
                            </Button>
                        </Link>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-bold">{{ campaign.name }}</h1>
                                <Badge :variant="getStatusVariant(campaign.status)">
                                    {{ getStatusText(campaign.status) }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground mt-2">
                                <div class="flex items-center gap-1">
                                    <Target class="h-4 w-4" />
                                    {{ campaign.activity_type }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <MapPin class="h-4 w-4" />
                                    {{ campaign.city }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <Calendar class="h-4 w-4" />
                                    {{ format(new Date(campaign.created_at), 'dd MMM yyyy à HH:mm', { locale: fr }) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button 
                        v-if="stats.total_contacts > 0"
                        variant="outline" 
                        @click="exportContacts"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Exporter
                    </Button>
                    <Button 
                        v-if="campaign.status === 'failed'"
                        variant="outline"
                        @click="restartCampaign"
                    >
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Relancer
                    </Button>
                    <Button 
                        variant="outline"
                        @click="deleteCampaign"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Supprimer
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Progression</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ Math.round(stats.progress) }}%</div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-blue-600 h-2 rounded-full transition-all"
                                    :style="{ width: `${Math.min(stats.progress, 100)}%` }"
                                ></div>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ stats.total_contacts }} / {{ campaign.target_count }} contacts
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Contacts</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_contacts }}</div>
                        <p class="text-xs text-muted-foreground">
                            Contacts collectés
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avec Email</CardTitle>
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.with_email }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.total_contacts > 0 ? Math.round((stats.with_email / stats.total_contacts) * 100) : 0 }}% du total
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avec Site Web</CardTitle>
                        <Globe class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.with_website }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.total_contacts > 0 ? Math.round((stats.with_website / stats.total_contacts) * 100) : 0 }}% du total
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Status Info -->
            <Card v-if="campaign.started_at || campaign.completed_at">
                <CardHeader>
                    <CardTitle>Informations de Status</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div v-if="campaign.started_at" class="flex items-center justify-between">
                        <span class="text-sm font-medium">Démarrée le :</span>
                        <span class="text-sm text-muted-foreground">
                            {{ format(new Date(campaign.started_at), 'dd MMM yyyy à HH:mm', { locale: fr }) }}
                        </span>
                    </div>
                    <div v-if="campaign.completed_at" class="flex items-center justify-between">
                        <span class="text-sm font-medium">Terminée le :</span>
                        <span class="text-sm text-muted-foreground">
                            {{ format(new Date(campaign.completed_at), 'dd MMM yyyy à HH:mm', { locale: fr }) }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Contacts -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Contacts Récents</CardTitle>
                    <Link href="/contacts" class="text-sm text-blue-600 hover:underline">
                        Voir tous
                    </Link>
                </CardHeader>
                <CardContent>
                    <div v-if="recent_contacts.length > 0" class="space-y-4">
                        <div 
                            v-for="contact in recent_contacts" 
                            :key="contact.id"
                            class="flex items-start justify-between p-4 border rounded-lg"
                        >
                            <div class="space-y-1">
                                <div class="font-medium">{{ contact.name }}</div>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                    <div v-if="contact.email" class="flex items-center gap-1">
                                        <Mail class="h-3 w-3" />
                                        {{ contact.email }}
                                    </div>
                                    <div v-if="contact.website" class="flex items-center gap-1">
                                        <Globe class="h-3 w-3" />
                                        <a :href="contact.website" target="_blank" class="hover:underline">
                                            {{ contact.website }}
                                        </a>
                                    </div>
                                </div>
                                <div v-if="contact.address" class="text-xs text-muted-foreground">
                                    {{ contact.address }}
                                </div>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ format(new Date(contact.created_at), 'dd/MM à HH:mm', { locale: fr }) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>Aucun contact trouvé pour le moment</p>
                        <p class="text-sm">{{ campaign.status === 'running' ? 'Le scraping est en cours...' : 'Vérifiez le status de la campagne' }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>