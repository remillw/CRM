<template>
  <AppLayout title="Modifier la liste">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Modifier {{ contactList?.name || 'Liste' }}
        </h2>
        <Link
          :href="route('contact-lists.index')"
          class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Retour aux listes
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 gap-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Nom de la liste *
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ex: Clients potentiels Paris"
                  />
                  <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                  </label>
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Description optionnelle de cette liste..."
                  ></textarea>
                  <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">
                    Statut
                  </label>
                  <div class="mt-2">
                    <label class="inline-flex items-center">
                      <input
                        v-model="form.status"
                        type="radio"
                        value="active"
                        class="form-radio text-blue-600"
                      />
                      <span class="ml-2">Active</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                      <input
                        v-model="form.status"
                        type="radio"
                        value="inactive"
                        class="form-radio text-blue-600"
                      />
                      <span class="ml-2">Inactive</span>
                    </label>
                  </div>
                </div>

                <div v-if="campaigns.length > 0">
                  <label class="block text-sm font-medium text-gray-700 mb-4">
                    Critères de sélection des contacts
                  </label>
                  
                  <!-- Sélection des campagnes -->
                  <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Campagnes</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-md p-3">
                      <label v-for="campaign in campaigns" :key="campaign.id" class="flex items-center">
                        <input
                          v-model="form.campaign_ids"
                          type="checkbox"
                          :value="campaign.id"
                          class="form-checkbox text-blue-600"
                        />
                        <span class="ml-2 text-sm">
                          {{ campaign.name }} ({{ campaign.activity_type }} - {{ campaign.city }})
                          <span class="text-gray-500">({{ campaign.contacts_count }} contacts)</span>
                        </span>
                      </label>
                    </div>
                  </div>

                  <!-- Filtres avancés -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Filtres sur le site web -->
                    <div class="border border-gray-200 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Site Web</h4>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.website_filter"
                            type="radio"
                            value="all"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Tous les contacts</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.website_filter"
                            type="radio"
                            value="with_website"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Avec site web seulement</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.website_filter"
                            type="radio"
                            value="without_website"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Sans site web</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.website_filter"
                            type="radio"
                            value="good_website"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Site web de qualité (site_good = true)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.website_filter"
                            type="radio"
                            value="bad_website"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Mauvais site web (site_good = false)</span>
                        </label>
                      </div>
                    </div>

                    <!-- Filtres sur la commande -->
                    <div class="border border-gray-200 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Capacité de commande</h4>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.command_filter"
                            type="radio"
                            value="all"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Tous les contacts</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.command_filter"
                            type="radio"
                            value="can_command"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Peuvent commander (can_command = true)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.command_filter"
                            type="radio"
                            value="cannot_command"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Ne peuvent pas commander (can_command = false)</span>
                        </label>
                      </div>
                    </div>

                    <!-- Filtres SEO -->
                    <div class="border border-gray-200 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Positionnement SEO</h4>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.seo_filter"
                            type="radio"
                            value="all"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Tous les contacts</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.seo_filter"
                            type="radio"
                            value="top_10"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Top 10 (position 1-10)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.seo_filter"
                            type="radio"
                            value="top_20"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Top 20 (position 1-20)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.seo_filter"
                            type="radio"
                            value="poor_ranking"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Mauvais classement (position 50+)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.seo_filter"
                            type="radio"
                            value="not_analyzed"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Non analysé</span>
                        </label>
                      </div>
                    </div>

                    <!-- Filtres sur les emails -->
                    <div class="border border-gray-200 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Email</h4>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.email_filter"
                            type="radio"
                            value="all"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Tous les contacts</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.email_filter"
                            type="radio"
                            value="with_email"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Avec email seulement</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.email_filter"
                            type="radio"
                            value="without_email"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Sans email</span>
                        </label>
                      </div>
                    </div>

                    <!-- Filtres sur les notes Google -->
                    <div class="border border-gray-200 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Note Google</h4>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.rating_filter"
                            type="radio"
                            value="all"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Toutes les notes</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.rating_filter"
                            type="radio"
                            value="excellent"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Excellente (4.5+)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.rating_filter"
                            type="radio"
                            value="good"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Bonne (4.0+)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.rating_filter"
                            type="radio"
                            value="poor"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Faible (<3.0)</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.rating_filter"
                            type="radio"
                            value="no_rating"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Sans note</span>
                        </label>
                      </div>
                    </div>

                    <!-- Filtres de vérification -->
                    <div class="border border-gray-200 rounded-lg p-4">
                      <h4 class="text-sm font-medium text-gray-900 mb-3">Vérification</h4>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.verified_filter"
                            type="radio"
                            value="all"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Tous les contacts</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.verified_filter"
                            type="radio"
                            value="verified"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Vérifiés seulement</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.verified_filter"
                            type="radio"
                            value="not_verified"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Non vérifiés</span>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                      💡 <strong>Mise à jour des filtres :</strong> Modifier les filtres mettra automatiquement à jour 
                      la liste des contacts selon les nouveaux critères.
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-6 space-x-3">
                <Link
                  :href="route('contact-lists.index')"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  Annuler
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Mettre à jour
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Gestion des contacts de la liste -->
        <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              Contacts dans cette liste ({{ contactList?.contacts_count || 0 }})
            </h3>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Toutes les campagnes</option>
                    <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                      {{ campaign.name }} ({{ campaign.contacts_count }} contacts)
                    </option>
                  </select>
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                  >
                    Ajouter depuis campagne
                  </button>
                </div>
                <Link
                  :href="route('contact-lists.export', contactList?.id)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Exporter
                </Link>
              </div>

              <div v-if="(contactList?.contacts_count || 0) === 0" class="text-center py-8 text-gray-500">
                Cette liste ne contient aucun contact.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface ContactList {
  id: number;
  name: string;
  description?: string;
  status: 'active' | 'inactive';
  contacts_count: number;
  filters?: {
    website_filter?: string;
    command_filter?: string;
    seo_filter?: string;
    email_filter?: string;
    rating_filter?: string;
    verified_filter?: string;
    campaign_ids?: number[];
  };
}

interface Campaign {
  id: number;
  name: string;
  activity_type: string;
  city: string;
  contacts_count: number;
}

const props = defineProps<{
  contactList: ContactList;
  campaigns: Campaign[];
}>();

const form = useForm({
  name: props.contactList?.name || '',
  description: props.contactList?.description || '',
  status: props.contactList?.status || 'active',
  campaign_ids: props.contactList?.filters?.campaign_ids || [] as number[],
  website_filter: props.contactList?.filters?.website_filter || 'all',
  command_filter: props.contactList?.filters?.command_filter || 'all',
  seo_filter: props.contactList?.filters?.seo_filter || 'all',
  email_filter: props.contactList?.filters?.email_filter || 'all',
  rating_filter: props.contactList?.filters?.rating_filter || 'all',
  verified_filter: props.contactList?.filters?.verified_filter || 'all'
});

const submit = () => {
  form.put(route('contact-lists.update', props.contactList?.id));
};
</script>