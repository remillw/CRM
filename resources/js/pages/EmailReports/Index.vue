<template>
  <AppLayout title="Récapitulatif des emails">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          📧 Récapitulatif des emails envoyés
        </h2>
        <div class="flex items-center space-x-3">
          <Link 
            :href="route('email-templates.index')"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Gérer les templates
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Statistiques globales -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ stats.total_sent }}</div>
            <div class="text-sm text-blue-600">Emails envoyés</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ stats.total_opened }}</div>
            <div class="text-sm text-green-600">Emails ouverts</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600">{{ stats.total_clicked }}</div>
            <div class="text-sm text-yellow-600">Liens cliqués</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-purple-600">{{ stats.open_rate }}%</div>
            <div class="text-sm text-purple-600">Taux d'ouverture</div>
          </div>
          <div class="bg-indigo-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-indigo-600">{{ stats.click_rate }}%</div>
            <div class="text-sm text-indigo-600">Taux de clic</div>
          </div>
        </div>

        <!-- Alertes de relance -->
        <div v-if="needFollowUp.length > 0" class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-orange-800 mb-3">
            ⚠️ {{ needFollowUp.length }} emails nécessitent une relance
          </h3>
          <div class="space-y-2">
            <div v-for="email in needFollowUp" :key="email.id" class="flex items-center justify-between bg-white p-3 rounded">
              <div>
                <div class="font-medium">{{ email.contact.business_name }}</div>
                <div class="text-sm text-gray-500">
                  Envoyé le {{ formatDate(email.sent_at) }} - Campagne: {{ email.campaign?.name }}
                </div>
              </div>
              <button 
                @click="sendFollowUp(email)"
                class="px-3 py-1 bg-orange-600 text-white rounded text-sm hover:bg-orange-700"
              >
                Relancer
              </button>
            </div>
          </div>
        </div>

        <!-- Templates les plus performants -->
        <div v-if="topTemplates.length > 0" class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Templates les plus performants</h3>
          <div class="space-y-3">
            <div v-for="template in topTemplates" :key="template.id" class="flex items-center justify-between p-3 bg-gray-50 rounded">
              <div>
                <div class="font-medium">{{ template.name }}</div>
                <div class="text-sm text-gray-500">{{ template.sent_count }} envois</div>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-green-600">{{ template.open_rate }}%</div>
                <div class="text-sm text-gray-500">taux d'ouverture</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistiques des tests -->
        <div v-if="testStats && testStats.total_sent > 0" class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-orange-800 mb-4">
            🧪 Emails de test - Statistiques de tracking
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-orange-600">{{ testStats.total_sent }}</div>
              <div class="text-sm text-orange-700">Emails de test envoyés</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ testStats.total_opened }}</div>
              <div class="text-sm text-green-700">Ouverts</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ testStats.total_clicked }}</div>
              <div class="text-sm text-blue-700">Clics</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">{{ testStats.open_rate }}%</div>
              <div class="text-sm text-purple-700">Taux d'ouverture</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-indigo-600">{{ testStats.click_rate }}%</div>
              <div class="text-sm text-indigo-700">Taux de clic</div>
            </div>
          </div>
        </div>

        <!-- Campagnes programmées récentes -->
        <div v-if="testCampaigns && testCampaigns.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">📅 Campagnes programmées récentes</h3>
            <p class="text-sm text-gray-600 mt-1">Incluant les campagnes de test</p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Campagne
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Emails envoyés
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Taux d'ouverture
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="campaign in testCampaigns" :key="campaign.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ campaign.name }}</div>
                    <div class="text-sm text-gray-500">{{ formatDate(campaign.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="campaign.is_test" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                      🧪 Test
                    </span>
                    <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      📧 Production
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ campaign.email_sends_count || 0 }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      {{ campaign.email_sends_count > 0 ? Math.round((campaign.test_opened_count / campaign.email_sends_count) * 100) : 0 }}%
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <Link 
                      :href="route('email-reports.campaign-schedule', campaign.id)"
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Voir détails
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Liste des campagnes -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Campagnes email par statut</h2>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Campagne
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Emails envoyés
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Taux d'ouverture
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Taux de clic
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Relances disponibles
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ campaign.name }}</div>
                      <div class="text-sm text-gray-500">{{ campaign.activity_type }} • {{ campaign.city }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ campaign.email_sends_count }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="text-sm font-medium text-gray-900">
                        {{ getOpenRate(campaign) }}%
                      </div>
                      <div class="ml-2 text-xs text-gray-500">
                        ({{ campaign.opened_count }}/{{ campaign.email_sends_count }})
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="text-sm font-medium text-gray-900">
                        {{ getClickRate(campaign) }}%
                      </div>
                      <div class="ml-2 text-xs text-gray-500">
                        ({{ campaign.clicked_count }}/{{ campaign.email_sends_count }})
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex space-x-2">
                      <span v-if="needsFirstFollowUp(campaign)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        Relance 1
                      </span>
                      <span v-if="needsSecondFollowUp(campaign)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                        Relance 2
                      </span>
                      <span v-if="needsOpenedFollowUp(campaign)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        Ouvert sans clic
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <Link 
                        :href="route('email-reports.campaign', campaign.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Détails
                      </Link>
                      <button 
                        @click="quickFollowUp(campaign)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Relancer
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Message si pas de campagnes -->
          <div v-if="campaigns.data.length === 0" class="px-6 py-12 text-center">
            <div class="text-gray-500">
              <div class="text-6xl mb-4">📧</div>
              <div class="text-xl mb-2">Aucune campagne email trouvée</div>
              <div class="text-sm mb-8">Créez votre première campagne email pour voir les statistiques</div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="campaigns.links.length > 3" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Affichage de {{ campaigns.from }} à {{ campaigns.to }} sur {{ campaigns.total }} campagnes
              </div>
              <div class="flex space-x-1">
                <Link
                  v-for="link in campaigns.links"
                  :key="link.label"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    'px-3 py-2 text-sm border rounded',
                    link.active
                      ? 'bg-blue-500 text-white border-blue-500'
                      : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                  ]"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Campaign {
  id: number
  name: string
  activity_type: string
  city: string
  email_sends_count: number
  opened_count: number
  clicked_count: number
}

interface EmailSend {
  id: number
  sent_at: string
  contact: {
    business_name: string
  }
  campaign?: {
    name: string
  }
}

interface Template {
  id: number
  name: string
  sent_count: number 
  opened_count: number
  open_rate: number
}

interface Props {
  campaigns: {
    data: Campaign[]
    links: any[]
    from: number
    to: number
    total: number
  }
  stats: {
    total_sent: number
    total_opened: number
    total_clicked: number
    open_rate: number
    click_rate: number
  }
  testStats?: {
    total_sent: number
    total_opened: number
    total_clicked: number
    open_rate: number
    click_rate: number
  }
  testCampaigns?: any[]
  needFollowUp: EmailSend[]
  topTemplates: Template[]
}

const props = defineProps<Props>()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getOpenRate = (campaign: Campaign) => {
  return campaign.email_sends_count > 0 
    ? Math.round((campaign.opened_count / campaign.email_sends_count) * 100)
    : 0
}

const getClickRate = (campaign: Campaign) => {
  return campaign.email_sends_count > 0 
    ? Math.round((campaign.clicked_count / campaign.email_sends_count) * 100)
    : 0
}

const needsFirstFollowUp = (campaign: Campaign) => {
  // Logique pour déterminer si la campagne a besoin d'une première relance
  return campaign.email_sends_count > 0 && campaign.opened_count < campaign.email_sends_count * 0.3
}

const needsSecondFollowUp = (campaign: Campaign) => {
  // Logique pour déterminer si la campagne a besoin d'une seconde relance
  return campaign.email_sends_count > 0 && campaign.opened_count < campaign.email_sends_count * 0.1
}

const needsOpenedFollowUp = (campaign: Campaign) => {
  // Logique pour déterminer si des emails ouverts n'ont pas eu de clic
  return campaign.opened_count > 0 && campaign.clicked_count < campaign.opened_count * 0.2
}

const sendFollowUp = (email: EmailSend) => {
  if (!confirm(`Envoyer une relance à ${email.contact.business_name} ?`)) return
  
  router.post(route('email-reports.send-follow-up', email.campaign?.id || 0), {
    template_name: 'Relance - Pas de réponse première prospection',
    contact_ids: [email.contact.id]
  })
}

const quickFollowUp = (campaign: Campaign) => {
  router.get(route('email-reports.campaign', campaign.id))
}
</script>