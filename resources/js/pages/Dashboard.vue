<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart3, Users, List, Mail, Plus, TrendingUp, Activity } from 'lucide-vue-next';

interface Props {
    stats: {
        total_campaigns: number;
        total_contacts: number;
        total_lists: number;
        total_email_campaigns: number;
        active_campaigns: number;
        recent_campaigns: Array<{
            id: number;
            name: string;
            status: string;
            activity_type: string;
            city: string;
            created_at: string;
        }>;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'text-green-600';
        case 'running': return 'text-blue-600';
        case 'failed': return 'text-red-600';
        default: return 'text-gray-600';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Campagnes</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_campaigns }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.active_campaigns }} en cours
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Contacts</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_contacts }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total scrapés
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Listes</CardTitle>
                        <List class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_lists }}</div>
                        <p class="text-xs text-muted-foreground">
                            Listes créées
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Emails</CardTitle>
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_email_campaigns }}</div>
                        <p class="text-xs text-muted-foreground">
                            Campagnes email
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Link :href="'/campaigns/create'">
                    <Button class="w-full h-20 flex flex-col gap-2">
                        <Plus class="h-6 w-6" />
                        Nouvelle Campagne
                    </Button>
                </Link>
                
                <Link :href="'/contact-lists/create'">
                    <Button variant="outline" class="w-full h-20 flex flex-col gap-2">
                        <List class="h-6 w-6" />
                        Créer une Liste
                    </Button>
                </Link>
                
                <Link :href="'/email-templates/create'">
                    <Button variant="outline" class="w-full h-20 flex flex-col gap-2">
                        <Mail class="h-6 w-6" />
                        Template Email
                    </Button>
                </Link>
                
                <Link :href="'/analytics'">
                    <Button variant="outline" class="w-full h-20 flex flex-col gap-2">
                        <TrendingUp class="h-6 w-6" />
                        Analytics
                    </Button>
                </Link>
            </div>

            <!-- Recent Campaigns -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Campagnes Récentes</CardTitle>
                        <CardDescription>
                            Vos dernières campagnes de scraping
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div 
                            v-for="campaign in stats.recent_campaigns" 
                            :key="campaign.id"
                            class="flex items-center justify-between p-3 border rounded-lg"
                        >
                            <div class="flex flex-col">
                                <div class="font-medium">{{ campaign.name }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ campaign.activity_type }} - {{ campaign.city }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Activity :class="['h-4 w-4', getStatusColor(campaign.status)]" />
                                <span :class="['text-sm capitalize', getStatusColor(campaign.status)]">
                                    {{ campaign.status }}
                                </span>
                            </div>
                        </div>
                        
                        <div v-if="stats.recent_campaigns.length === 0" class="text-center py-4 text-muted-foreground">
                            Aucune campagne créée
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Prochaines Actions</CardTitle>
                        <CardDescription>
                            Suggestions d'actions
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="stats.total_campaigns === 0" class="space-y-2">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="font-medium text-blue-900">Commencez votre premier scraping</div>
                                <div class="text-sm text-blue-700">Créez une campagne pour scraper Google Maps</div>
                            </div>
                        </div>
                        
                        <div v-if="stats.total_contacts > 0 && stats.total_lists === 0" class="space-y-2">
                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="font-medium text-green-900">Organisez vos contacts</div>
                                <div class="text-sm text-green-700">Créez des listes pour organiser vos {{ stats.total_contacts }} contacts</div>
                            </div>
                        </div>
                        
                        <div v-if="stats.total_lists > 0 && stats.total_email_campaigns === 0" class="space-y-2">
                            <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                <div class="font-medium text-purple-900">Lancez votre première campagne email</div>
                                <div class="text-sm text-purple-700">Créez un template et envoyez des emails à vos listes</div>
                            </div>
                        </div>
                        
                        <div v-if="stats.total_campaigns > 0 && stats.total_lists > 0 && stats.total_email_campaigns > 0" class="space-y-2">
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="font-medium text-gray-900">Tout fonctionne bien !</div>
                                <div class="text-sm text-gray-700">Consultez vos analytics pour optimiser vos campagnes</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
