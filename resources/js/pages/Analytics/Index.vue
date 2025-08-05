<template>
  <AppLayout title="Analytics">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Analytics & Statistiques
        </h2>
        <div class="flex items-center space-x-4">
          <select v-model="selectedPeriod" @change="updatePeriod" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            <option value="7">7 derniers jours</option>
            <option value="30">30 derniers jours</option>
            <option value="90">90 derniers jours</option>
          </select>
          <Link
            :href="route('analytics.export', { period: selectedPeriod })"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Métriques principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Contacts</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ metrics.total_contacts.toLocaleString() }}</dd>
                    <dd class="text-xs text-green-600">+{{ metrics.recent_contacts }} récents</dd>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Campagnes</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ metrics.total_campaigns }}</dd>
                    <dd class="text-xs text-blue-600">{{ metrics.completed_campaigns }} terminées</dd>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Avec Email</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ metrics.contacts_with_email }}</dd>
                    <dd class="text-xs text-gray-600">{{ Math.round((metrics.contacts_with_email / metrics.total_contacts) * 100) }}% du total</dd>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Note Moyenne</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ metrics.average_rating }}/5</dd>
                    <dd class="text-xs text-gray-600">Google Reviews</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Évolution des contacts -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des contacts (30 derniers jours)</h3>
          <div class="h-64">
            <div v-if="contactsEvolution.length === 0" class="h-full flex items-center justify-center bg-gray-50 rounded">
              <p class="text-gray-500">Aucune donnée disponible pour cette période</p>
            </div>
            <div v-else class="space-y-2">
              <div v-for="day in contactsEvolution.slice(-7)" :key="day.date" class="flex items-center justify-between py-2 px-4 bg-gray-50 rounded">
                <span class="text-sm text-gray-600">{{ new Date(day.date).toLocaleDateString('fr-FR') }}</span>
                <span class="text-sm font-medium text-gray-900">{{ day.count }} contacts</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistiques par activité et ville -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par type d'activité</h3>
            <div class="space-y-3">
              <div v-for="activity in activitiesStats" :key="activity.activity" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900 capitalize">{{ activity.activity }}</span>
                </div>
                <div class="text-right">
                  <span class="text-sm font-medium text-gray-900">{{ activity.contacts }} contacts</span>
                  <span class="text-xs text-gray-500 block">{{ activity.campaigns }} campagnes</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top villes</h3>
            <div class="space-y-3">
              <div v-for="city in citiesStats.slice(0, 8)" :key="city.city" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                  <span class="text-sm font-medium text-gray-900">{{ city.city }}</span>
                </div>
                <div class="text-right">
                  <span class="text-sm font-medium text-gray-900">{{ city.contacts }} contacts</span>
                  <span class="text-xs text-gray-500 block">{{ city.campaigns }} campagnes</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Qualité des contacts -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Qualité des contacts (notes Google)</h3>
          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ qualityStats.excellent }}</div>
              <div class="text-sm text-gray-600">Excellents (4.5+)</div>
            </div>
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <div class="text-2xl font-bold text-blue-600">{{ qualityStats.good }}</div>
              <div class="text-sm text-gray-600">Bons (4.0-4.4)</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
              <div class="text-2xl font-bold text-yellow-600">{{ qualityStats.average }}</div>
              <div class="text-sm text-gray-600">Moyens (3.0-3.9)</div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
              <div class="text-2xl font-bold text-red-600">{{ qualityStats.poor }}</div>
              <div class="text-sm text-gray-600">Faibles (<3.0)</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
              <div class="text-2xl font-bold text-gray-600">{{ qualityStats.no_rating }}</div>
              <div class="text-sm text-gray-600">Non notés</div>
            </div>
          </div>
        </div>

        <!-- Campagnes récentes -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Campagnes récentes</h3>
              <Link :href="route('analytics.campaigns')" class="text-sm text-blue-600 hover:text-blue-800">
                Voir toutes les campagnes
              </Link>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campagne</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objectif/Résultat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taux de succès</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qualité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="campaign in recentCampaigns.slice(0, 5)" :key="campaign.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ campaign.name }}</div>
                      <div class="text-sm text-gray-500">{{ campaign.activity_type }} - {{ campaign.city }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ campaign.actual_count }}/{{ campaign.target_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900">{{ campaign.success_rate }}%</div>
                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                          <div :class="[
                            'h-2 rounded-full',
                            campaign.success_rate >= 80 ? 'bg-green-500' :
                            campaign.success_rate >= 50 ? 'bg-yellow-500' : 'bg-red-500'
                          ]" :style="`width: ${Math.min(campaign.success_rate, 100)}%`"></div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        <div class="flex items-center">
                          <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                          </svg>
                          {{ campaign.avg_rating }}
                        </div>
                        <div class="text-xs text-gray-500">{{ campaign.with_email }} emails</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        campaign.status === 'completed' ? 'bg-green-100 text-green-800' :
                        campaign.status === 'running' ? 'bg-blue-100 text-blue-800' :
                        campaign.status === 'failed' ? 'bg-red-100 text-red-800' :
                        'bg-gray-100 text-gray-800'
                      ]">
                        {{ campaign.status === 'completed' ? 'Terminée' :
                           campaign.status === 'running' ? 'En cours' :
                           campaign.status === 'failed' ? 'Échouée' : 'En attente' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Taux de succès par activité -->
        <div v-if="successRates.length > 0" class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Taux de succès par type d'activité</h3>
          <div class="space-y-4">
            <div v-for="rate in successRates" :key="rate.activity" class="flex items-center justify-between">
              <div class="flex items-center">
                <span class="text-sm font-medium text-gray-900 capitalize min-w-24">{{ rate.activity }}</span>
                <span class="text-sm text-gray-500 ml-2">({{ rate.campaigns }} campagnes)</span>
              </div>
              <div class="flex items-center">
                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                  <div 
                    :class="[
                      'h-2 rounded-full',
                      rate.success_rate >= 80 ? 'bg-green-500' :
                      rate.success_rate >= 60 ? 'bg-yellow-500' : 'bg-red-500'
                    ]" 
                    :style="`width: ${Math.min(rate.success_rate, 100)}%`"
                  ></div>
                </div>
                <span class="text-sm font-medium text-gray-900 min-w-12">{{ rate.success_rate }}%</span>
              </div>
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

interface Metrics {
  total_contacts: number;
  total_campaigns: number;
  completed_campaigns: number;
  contacts_with_email: number;
  contacts_with_website: number;
  average_rating: number;
  total_emails_sent: number;
  recent_contacts: number;
}

interface ContactEvolution {
  date: string;
  count: number;
}

interface ActivityStats {
  activity: string;
  campaigns: number;
  contacts: number;
}

interface CityStats {
  city: string;
  campaigns: number;
  contacts: number;
}

interface RecentCampaign {
  id: number;
  name: string;
  activity_type: string;
  city: string;
  target_count: number;
  actual_count: number;
  success_rate: number;
  with_email: number;
  with_website: number;
  avg_rating: number;
  created_at: string;
  status: string;
}

interface QualityStats {
  excellent: number;
  good: number;
  average: number;
  poor: number;
  no_rating: number;
}

interface SuccessRate {
  activity: string;
  success_rate: number;
  campaigns: number;
}

const props = defineProps<{
  metrics: Metrics;
  contactsEvolution: ContactEvolution[];
  activitiesStats: ActivityStats[];
  citiesStats: CityStats[];
  recentCampaigns: RecentCampaign[];
  qualityStats: QualityStats;
  successRates: SuccessRate[];
  period: string;
}>();

const selectedPeriod = ref(props.period);

const updatePeriod = () => {
  router.get(route('analytics'), { period: selectedPeriod.value });
};
</script>