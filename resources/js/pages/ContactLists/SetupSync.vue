<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Settings, Sync, Target, Filter } from 'lucide-vue-next';

interface Campaign {
    id: number;
    name: string;
    activity_type: string;
    city: string;
    contacts_count: number;
}

interface ContactList {
    id: number;
    name: string;
    description: string;
    auto_sync: boolean;
    sync_campaign_id?: number;
    sync_criteria?: {
        site_good?: boolean;
        can_command?: boolean;
        has_email?: boolean;
        has_website?: boolean;
    };
    last_synced_at?: string;
    synced_contacts_count: number;
}

interface Props {
    contactList: ContactList;
    campaigns: Campaign[];
}

const props = defineProps<Props>();

const form = useForm({
    sync_campaign_id: props.contactList.sync_campaign_id || '',
    auto_sync: props.contactList.auto_sync || false,
    sync_criteria: {
        site_good: props.contactList.sync_criteria?.site_good || false,
        can_command: props.contactList.sync_criteria?.can_command || false,
        has_email: props.contactList.sync_criteria?.has_email || false,
        has_website: props.contactList.sync_criteria?.has_website || false,
    },
    sync_now: false,
});

const submit = () => {
    form.post(route('contact-lists.store-sync', props.contactList.id));
};

const selectedCampaign = computed(() => {
    if (!form.sync_campaign_id) return null;
    return props.campaigns.find(c => c.id === parseInt(form.sync_campaign_id.toString()));
});
</script>

<template>
    <Head :title="`Configuration Sync - ${contactList.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Button variant="outline" size="sm" @click="$inertia.visit(route('contact-lists.show', contactList.id))">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Retour à la liste
                </Button>
                <div>
                    <h2 class="text-2xl font-bold">Configuration de la Synchronisation</h2>
                    <p class="text-muted-foreground">{{ contactList.name }}</p>
                </div>
            </div>
        </template>

        <div class="p-6 space-y-6">
            <!-- État actuel -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Settings class="h-5 w-5" />
                        État Actuel
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ contactList.synced_contacts_count }}
                            </div>
                            <div class="text-sm text-muted-foreground">Contacts synchronisés</div>
                        </div>
                        <div class="text-center">
                            <Badge :variant="contactList.auto_sync ? 'default' : 'outline'">
                                {{ contactList.auto_sync ? 'Auto-sync activé' : 'Manuel seulement' }}
                            </Badge>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-muted-foreground">
                                {{ contactList.last_synced_at ? 'Dernière sync: ' + new Date(contactList.last_synced_at).toLocaleString('fr-FR') : 'Jamais synchronisé' }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Configuration -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Target class="h-5 w-5" />
                                Configuration de la Synchronisation
                            </CardTitle>
                            <CardDescription>
                                Définissez comment synchroniser automatiquement cette liste avec une campagne
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Campagne source -->
                                <div class="space-y-2">
                                    <Label for="sync_campaign_id">Campagne source</Label>
                                    <Select v-model="form.sync_campaign_id" required>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionnez une campagne..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="campaign in campaigns" 
                                                :key="campaign.id" 
                                                :value="campaign.id.toString()"
                                            >
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ campaign.name }}</span>
                                                    <span class="text-sm text-muted-foreground">
                                                        {{ campaign.activity_type }} • {{ campaign.city }} • {{ campaign.contacts_count }} contacts
                                                    </span>
                                                </div>
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.sync_campaign_id" class="text-sm text-red-600">
                                        {{ form.errors.sync_campaign_id }}
                                    </div>
                                </div>

                                <!-- Critères de filtrage -->
                                <div class="space-y-4">
                                    <Label class="flex items-center gap-2">
                                        <Filter class="h-4 w-4" />
                                        Critères de synchronisation
                                    </Label>
                                    
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="flex items-center space-x-2">
                                            <Checkbox 
                                                id="site_good" 
                                                v-model:checked="form.sync_criteria.site_good"
                                            />
                                            <Label for="site_good" class="font-normal">
                                                Site marqué comme "bon" seulement
                                            </Label>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <Checkbox 
                                                id="can_command" 
                                                v-model:checked="form.sync_criteria.can_command"
                                            />
                                            <Label for="can_command" class="font-normal">
                                                Peut commander en ligne
                                            </Label>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <Checkbox 
                                                id="has_email" 
                                                v-model:checked="form.sync_criteria.has_email"
                                            />
                                            <Label for="has_email" class="font-normal">
                                                Possède un email
                                            </Label>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <Checkbox 
                                                id="has_website" 
                                                v-model:checked="form.sync_criteria.has_website"
                                            />
                                            <Label for="has_website" class="font-normal">
                                                Possède un site web
                                            </Label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Options -->
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="auto_sync" 
                                            v-model:checked="form.auto_sync"
                                        />
                                        <Label for="auto_sync" class="font-normal">
                                            Synchronisation automatique (mise à jour régulière)
                                        </Label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="sync_now" 
                                            v-model:checked="form.sync_now"
                                        />
                                        <Label for="sync_now" class="font-normal">
                                            Synchroniser immédiatement après sauvegarde
                                        </Label>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <Button type="submit" :disabled="form.processing">
                                        <Sync class="mr-2 h-4 w-4" />
                                        {{ form.processing ? 'Configuration...' : 'Configurer la synchronisation' }}
                                    </Button>
                                    
                                    <Button 
                                        type="button" 
                                        variant="outline"
                                        @click="$inertia.visit(route('contact-lists.show', contactList.id))"
                                    >
                                        Annuler
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Aperçu -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle>Aperçu</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="selectedCampaign" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="font-medium text-blue-900 mb-2">Campagne sélectionnée</div>
                                <div class="text-sm text-blue-700">
                                    <div class="font-medium">{{ selectedCampaign.name }}</div>
                                    <div>{{ selectedCampaign.activity_type }} • {{ selectedCampaign.city }}</div>
                                    <div>{{ selectedCampaign.contacts_count }} contacts disponibles</div>
                                </div>
                            </div>

                            <div v-if="Object.values(form.sync_criteria).some(v => v)" class="space-y-2">
                                <div class="font-medium text-sm">Filtres appliqués :</div>
                                <div class="space-y-1">
                                    <Badge v-if="form.sync_criteria.site_good" variant="secondary" class="text-xs">
                                        Site bon
                                    </Badge>
                                    <Badge v-if="form.sync_criteria.can_command" variant="secondary" class="text-xs">
                                        Commande en ligne
                                    </Badge>
                                    <Badge v-if="form.sync_criteria.has_email" variant="secondary" class="text-xs">
                                        Avec email
                                    </Badge>
                                    <Badge v-if="form.sync_criteria.has_website" variant="secondary" class="text-xs">
                                        Avec site web
                                    </Badge>
                                </div>
                            </div>

                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="font-medium text-green-900 mb-1">Comment ça marche ?</div>
                                <div class="text-sm text-green-700">
                                    Les contacts de la campagne correspondant aux critères seront automatiquement ajoutés à cette liste.
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>