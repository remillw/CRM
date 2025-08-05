<template>
  <AppLayout title="Campagnes Email Programmées">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          📅 Campagnes Email Programmées
        </h2>
        <div class="flex items-center space-x-3">
          <Link 
            :href="route('email-campaign-schedules.create')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            <Plus class="w-4 h-4 mr-2" />
            Nouvelle campagne
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ stats.total_schedules }}</div>
            <div class="text-sm text-blue-600">Campagnes totales</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600">{{ stats.scheduled_count }}</div>
            <div class="text-sm text-yellow-600">Programmées</div>
          </div>
          <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-orange-600">{{ stats.sending_count }}</div>
            <div class="text-sm text-orange-600">En envoi</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ stats.sent_count }}</div>
            <div class="text-sm text-green-600">Envoyées</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-red-600">{{ stats.due_count }}</div>
            <div class="text-sm text-red-600">À envoyer maintenant</div>
          </div>
        </div>

        <!-- Alertes pour campagnes dues -->
        <div v-if="stats.due_count > 0" class="bg-orange-50 border border-orange-200 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-orange-800 mb-3">
            ⚠️ {{ stats.due_count }} campagne(s) prête(s) à être envoyée(s)
          </h3>
          <p class="text-orange-700 mb-3">
            Des campagnes programmées sont arrivées à échéance et peuvent être envoyées maintenant.
          </p>
          <button 
            @click="sendAllDue"
            class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700"
          >
            🚀 Envoyer toutes les campagnes dues
          </button>
        </div>

        <!-- Actions rapides -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow p-6 text-white mb-6">
          <h2 class="text-lg font-semibold mb-4">🚀 Actions rapides</h2>
          <div class="flex flex-wrap gap-3">
            <Link
              :href="route('email-campaign-schedules.create')"
              class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-lg transition-colors font-medium shadow-lg"
            >
              ✨ Créer une campagne email
            </Link>
            <Link
              :href="route('email-templates.index')"
              class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
            >
              📝 Gérer les templates
            </Link>
            <Link
              :href="route('contact-lists.index')"
              class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
            >
              📋 Gérer les listes
            </Link>
            <Link
              :href="route('email-reports.index')"
              class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
            >
              📊 Voir les statistiques
            </Link>
          </div>
        </div>

        <!-- Liste des campagnes -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h2 class="text-lg font-medium text-gray-900">Mes campagnes email programmées</h2>
              <div class="flex items-center space-x-3">
                <label class="flex items-center text-sm text-gray-600">
                  <input
                    v-model="showTestCampaigns"
                    type="checkbox"
                    class="form-checkbox text-blue-600 h-4 w-4 mr-2"
                  />
                  Afficher les tests
                </label>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Campagne
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Template
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Destinataires
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Programmation
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Progrès
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="schedule in filteredSchedules" :key="schedule.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ schedule.name }}</div>
                      <div v-if="schedule.description" class="text-sm text-gray-500">{{ schedule.description }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ schedule.template.name }}</div>
                    <div class="text-xs text-gray-500">{{ schedule.template.segment_type }}</div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ schedule.total_recipients }} contacts
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ formatDate(schedule.scheduled_at) }}</div>
                    <div class="text-xs" :class="getTimeClass(schedule.scheduled_at)">
                      {{ getTimeStatus(schedule.scheduled_at) }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span 
                      :class="getStatusClass(schedule.status)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getStatusLabel(schedule.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="schedule.status === 'sending' || schedule.status === 'sent'" class="w-full">
                      <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                        <span>{{ schedule.sent_count }}/{{ schedule.total_recipients }}</span>
                        <span>{{ getProgressPercentage(schedule) }}%</span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                          class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                          :style="{ width: getProgressPercentage(schedule) + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div v-else class="text-xs text-gray-400">En attente</div>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <Link 
                        :href="route('email-reports.campaign-schedule', schedule.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Détails
                      </Link>
                      <Link 
                        :href="route('email-campaign-schedules.edit', schedule.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Modifier
                      </Link>
                      <button 
                        v-if="schedule.status === 'scheduled'"
                        @click="sendNow(schedule)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Envoyer
                      </button>
                      <button 
                        @click="duplicateSchedule(schedule)"
                        class="text-purple-600 hover:text-purple-900"
                      >
                        Dupliquer
                      </button>
                      <button 
                        v-if="schedule.status !== 'sending'"
                        @click="deleteSchedule(schedule)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Supprimer
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Message si pas de campagnes -->
          <div v-if="schedules.data.length === 0" class="px-6 py-12 text-center">
            <div class="text-gray-500">
              <div class="text-6xl mb-4">📅</div>
              <div class="text-xl mb-2">Aucune campagne email programmée</div>
              <div class="text-sm mb-8">Créez votre première campagne email pour automatiser vos envois</div>
              <Link
                :href="route('email-campaign-schedules.create')"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-lg"
              >
                <Plus class="w-5 h-5 mr-2" />
                Créer ma première campagne
              </Link>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="schedules.links.length > 3" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Affichage de {{ schedules.from }} à {{ schedules.to }} sur {{ schedules.total }} campagnes
              </div>
              <div class="flex space-x-1">
                <Link
                  v-for="link in schedules.links"
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
import { Plus } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

interface EmailCampaignSchedule {
  id: number
  name: string
  description?: string
  scheduled_at: string
  status: 'scheduled' | 'sending' | 'sent' | 'failed'
  total_recipients: number
  sent_count: number
  failed_count: number
  is_test: boolean
  template: {
    name: string
    segment_type: string
  }
}

interface Props {
  schedules: {
    data: EmailCampaignSchedule[]
    links: any[]
    from: number
    to: number
    total: number
  }
  stats: {
    total_schedules: number
    scheduled_count: number
    sending_count: number
    sent_count: number
    due_count: number
  }
}

const props = defineProps<Props>()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTimeStatus = (scheduledAt: string) => {
  const now = new Date()
  const scheduled = new Date(scheduledAt)
  const diffHours = (scheduled.getTime() - now.getTime()) / (1000 * 60 * 60)
  
  if (diffHours < 0) {
    return 'En retard'
  } else if (diffHours < 1) {
    return 'Dans moins d\'1h'
  } else if (diffHours < 24) {
    return `Dans ${Math.floor(diffHours)}h`
  } else {
    const diffDays = Math.floor(diffHours / 24)
    return `Dans ${diffDays} jour${diffDays > 1 ? 's' : ''}`
  }
}

const getTimeClass = (scheduledAt: string) => {
  const now = new Date()
  const scheduled = new Date(scheduledAt)
  const diffHours = (scheduled.getTime() - now.getTime()) / (1000 * 60 * 60)
  
  if (diffHours < 0) {
    return 'text-red-600'
  } else if (diffHours < 1) {
    return 'text-orange-600'
  } else {
    return 'text-gray-500'
  }
}

const getStatusLabel = (status: string) => {
  const labels = {
    scheduled: 'Programmée',
    sending: 'En envoi',
    sent: 'Envoyée',
    failed: 'Échec'
  }
  return labels[status] || status
}

const getStatusClass = (status: string) => {
  const classes = {
    scheduled: 'bg-yellow-100 text-yellow-800',
    sending: 'bg-blue-100 text-blue-800',
    sent: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getProgressPercentage = (schedule: EmailCampaignSchedule) => {
  if (schedule.total_recipients === 0) return 0
  return Math.round((schedule.sent_count / schedule.total_recipients) * 100)
}

const sendNow = (schedule: EmailCampaignSchedule) => {
  if (!confirm(`Envoyer la campagne "${schedule.name}" maintenant à ${schedule.total_recipients} destinataires ?`)) return
  
  router.post(route('email-campaign-schedules.send-now', schedule.id))
}

const duplicateSchedule = (schedule: EmailCampaignSchedule) => {
  if (!confirm(`Dupliquer la campagne "${schedule.name}" ?`)) return
  
  router.post(route('email-campaign-schedules.duplicate', schedule.id))
}

const sendAllDue = () => {
  if (!confirm(`Envoyer toutes les ${props.stats.due_count} campagnes dues maintenant ?`)) return
  
  router.post(route('email-campaign-schedules.send-all-due'))
}

const deleteSchedule = (schedule: EmailCampaignSchedule) => {
  const confirmMessage = schedule.is_test 
    ? `Supprimer la campagne de test "${schedule.name}" ?`
    : `Supprimer définitivement la campagne "${schedule.name}" ?\n\nCette action ne peut pas être annulée.`
  
  if (!confirm(confirmMessage)) return
  
  router.delete(route('email-campaign-schedules.destroy', schedule.id))
}

// Filtre pour les campagnes de test
const showTestCampaigns = ref(false)

const filteredSchedules = computed(() => {
  if (showTestCampaigns.value) {
    return props.schedules.data
  }
  return props.schedules.data.filter(schedule => !schedule.is_test)
})
</script>