<template>
  <AppLayout title="Contacts">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Contacts ({{ contacts.total }})
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('seo-queries.index')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 border border-transparent rounded-md text-sm font-medium text-white hover:from-green-700 hover:to-teal-700 shadow-lg"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            📊 Requêtes SEO
          </Link>
          <button
            @click="analyzeAllContactsSeo"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:from-purple-700 hover:to-blue-700 shadow-lg"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            🚀 Analyser SEO (tous avec site)
          </button>
          <select 
            v-model="selectedCampaignForSeo" 
            @change="analyzeCampaignSeo"
            class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50"
          >
            <option value="">Analyser SEO par campagne</option>
            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
              {{ campaign.name }} ({{ campaign.activity_type }} - {{ campaign.city }})
            </option>
          </select>
          <Link
            :href="route('contacts.export')"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter CSV
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.total }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Avec Email</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.with_email }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Avec Site Web</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.with_website }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Vérifiés</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.verified }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions en lot -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">Actions SEO en lot</h3>
              <p class="text-sm text-gray-600">Analysez le positionnement SEO de vos contacts par lot</p>
            </div>
            <div class="flex space-x-3">
              <button
                @click="analyzeAllContactsSeo"
                class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-purple-700"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Analyser cette page ({{ contacts.data.filter(c => c.website).length }} sites)
              </button>
              <select 
                v-model="selectedCampaignForSeo" 
                @change="analyzeCampaignSeo"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50"
              >
                <option value="">Choisir une campagne...</option>
                <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                  {{ campaign.name }} ({{ campaign.activity_type }} - {{ campaign.city }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Table des contacts -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">
                Liste des contacts
              </h3>
              <div class="flex items-center space-x-4">
                <select 
                  v-model="selectedCampaignFilter" 
                  @change="filterByCampaign"
                  class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                  <option value="">Toutes les campagnes</option>
                  <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                    {{ campaign.name }} ({{ campaign.activity_type }} - {{ campaign.city }})
                  </option>
                </select>
                <input
                  type="search"
                  placeholder="Rechercher..."
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Entreprise
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Adresse
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Note
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Campagne
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Statut
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      SEO
                    </th>
                    <th class="relative px-6 py-3">
                      <span class="sr-only">Actions</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-if="contacts.data.length === 0">
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                      Aucun contact disponible. 
                      <Link :href="route('campaigns.create')" class="text-blue-600 hover:text-blue-800">
                        Créez une campagne
                      </Link>
                      pour commencer à scraper des contacts.
                    </td>
                  </tr>
                  <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ contact.business_name }}
                          </div>
                          <div v-if="contact.is_verified" class="text-xs text-green-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Vérifié
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        <div v-if="contact.phone" class="flex items-center mb-1">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                          </svg>
                          {{ contact.phone }}
                        </div>
                        <div v-if="contact.email" class="flex items-center mb-1">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                          </svg>
                          {{ contact.email }}
                        </div>
                        <div v-if="contact.website" class="flex items-center">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                          </svg>
                          <a :href="contact.website" target="_blank" class="text-blue-600 hover:text-blue-800 truncate max-w-xs">
                            {{ contact.website }}
                          </a>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900 max-w-xs truncate">
                        {{ contact.address }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="contact.google_rating" class="flex items-center">
                        <div class="flex items-center">
                          <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                          </svg>
                          <span class="ml-1 text-sm text-gray-900">{{ contact.google_rating }}</span>
                        </div>
                        <span class="ml-2 text-xs text-gray-500">({{ contact.review_count }} avis)</span>
                      </div>
                      <div v-else class="text-sm text-gray-400">Non noté</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ contact.campaign?.name }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ contact.campaign?.activity_type }} - {{ contact.campaign?.city }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-col space-y-2">
                        <div class="flex items-center">
                          <span class="text-xs text-gray-600 mr-2">Site Good:</span>
                          <button
                            @click="toggleSiteGood(contact)"
                            :class="[
                              'relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2',
                              contact.site_good ? 'bg-green-600' : 'bg-gray-200'
                            ]"
                          >
                            <span
                              :class="[
                                'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                contact.site_good ? 'translate-x-4' : 'translate-x-0'
                              ]"
                            />
                          </button>
                        </div>
                        <div class="flex items-center">
                          <span class="text-xs text-gray-600 mr-2">Can Command:</span>
                          <button
                            @click="toggleCanCommand(contact)"
                            :class="[
                              'relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2',
                              contact.can_command ? 'bg-green-600' : 'bg-gray-200'
                            ]"
                          >
                            <span
                              :class="[
                                'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                contact.can_command ? 'translate-x-4' : 'translate-x-0'
                              ]"
                            />
                          </button>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-col space-y-1">
                        <div v-if="contact.seo_position" class="flex items-center">
                          <span class="text-xs font-medium text-green-600">
                            Position: {{ contact.seo_position }}
                          </span>
                        </div>
                        <div v-else-if="contact.seo_analyzed_at" class="text-xs text-gray-500">
                          Non classé
                        </div>
                        <div v-else-if="contact.website" class="text-xs text-gray-400">
                          Non analysé
                        </div>
                        <div v-else class="text-xs text-gray-400">
                          Pas de site
                        </div>
                        
                        <button
                          v-if="contact.website"
                          @click="analyzeSeo(contact)"
                          class="text-xs text-blue-600 hover:text-blue-800 text-left"
                        >
                          {{ contact.seo_analyzed_at ? 'Re-analyser' : 'Analyser SEO' }}
                        </button>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link
                        :href="route('contacts.show', contact.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                      >
                        Voir
                      </Link>
                      <Link
                        :href="route('contacts.edit', contact.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Modifier
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="contacts.links" class="mt-6">
              <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Link
                    v-if="contacts.prev_page_url"
                    :href="contacts.prev_page_url"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Précédent
                  </Link>
                  <Link
                    v-if="contacts.next_page_url"
                    :href="contacts.next_page_url"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Suivant
                  </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Affichage de
                      <span class="font-medium">{{ contacts.from }}</span>
                      à
                      <span class="font-medium">{{ contacts.to }}</span>
                      sur
                      <span class="font-medium">{{ contacts.total }}</span>
                      résultats
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <template v-for="(link, index) in contacts.links" :key="index">
                        <Link
                          v-if="link.url"
                          :href="link.url"
                          :class="[
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                            link.active
                              ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            index === 0 ? 'rounded-l-md' : '',
                            index === contacts.links.length - 1 ? 'rounded-r-md' : ''
                          ]"
                          v-html="link.label"
                        />
                        <span
                          v-else
                          :class="[
                            'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500',
                            index === 0 ? 'rounded-l-md' : '',
                            index === contacts.links.length - 1 ? 'rounded-r-md' : ''
                          ]"
                          v-html="link.label"
                        />
                      </template>
                    </nav>
                  </div>
                </div>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Contact {
  id: number;
  business_name: string;
  phone?: string;
  email?: string;
  website?: string;
  address?: string;
  google_rating?: number;
  review_count?: number;
  is_verified: boolean;
  site_good: boolean;
  can_command: boolean;
  seo_position?: number;
  seo_analyzed_at?: string;
  campaign?: {
    id: number;
    name: string;
    activity_type: string;
    city: string;
  };
}

interface Stats {
  total: number;
  with_email: number;
  with_website: number;
  verified: number;
}

interface Campaign {
  id: number;
  name: string;
  activity_type: string;
  city: string;
}

interface PaginatedContacts {
  data: Contact[];
  links: any[];
  total: number;
  from: number;
  to: number;
  prev_page_url?: string;
  next_page_url?: string;
}

const props = defineProps<{
  contacts: PaginatedContacts;
  stats: Stats;
  campaigns: Campaign[];
  filters?: any;
}>();

const toggleSiteGood = (contact: Contact) => {
  router.post(route('contacts.toggle-site-good', contact.id));
};

const toggleCanCommand = (contact: Contact) => {
  router.post(route('contacts.toggle-can-command', contact.id));
};

const selectedCampaignForSeo = ref('');
const selectedCampaignFilter = ref(props.filters?.campaign || '');

const analyzeSeo = (contact: Contact) => {
  router.post(route('contacts.analyze-seo', contact.id));
};

const analyzeCampaignSeo = () => {
  if (selectedCampaignForSeo.value) {
    router.post(route('contacts.analyze-campaign-seo'), {
      campaign_id: selectedCampaignForSeo.value
    });
    selectedCampaignForSeo.value = '';
  }
};

const analyzeAllContactsSeo = () => {
  const allContactIds = props.contacts.data
    .filter(contact => contact.website)
    .map(contact => contact.id);
  
  if (allContactIds.length === 0) {
    alert('Aucun contact avec site web trouvé sur cette page.');
    return;
  }
  
  if (confirm(`Lancer l'analyse SEO pour ${allContactIds.length} contacts avec site web sur cette page ?`)) {
    router.post(route('contacts.bulk-analyze-seo'), {
      contact_ids: allContactIds
    });
  }
};

const filterByCampaign = () => {
  console.log('Filtrage par campagne:', selectedCampaignFilter.value);
  const params = selectedCampaignFilter.value ? { campaign: selectedCampaignFilter.value } : {};
  router.get(route('contacts.index'), params, {
    preserveState: false,
    preserveScroll: false
  });
};
</script>