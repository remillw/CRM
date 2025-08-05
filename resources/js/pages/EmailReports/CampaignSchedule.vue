<template>
  <AppLayout :title="`Détails - ${campaign.name}`">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          📊 {{ campaign.name }}
        </h2>
        <div class="flex items-center space-x-3">
          <Link 
            :href="route('email-campaign-schedules.index')"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
          >
            Retour aux campagnes
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Statistiques de la campagne -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ stats.total_sent }}</div>
            <div class="text-sm text-blue-600">Emails envoyés</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ stats.total_opened }}</div>
            <div class="text-sm text-green-600">Emails ouverts</div>
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

        <!-- Section Relance -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">🚀 Relance sélective</h3>
            <p class="text-sm text-gray-600 mt-1">Sélectionnez les contacts à relancer et choisissez votre template</p>
          </div>
          
          <div class="px-6 py-4">
            <form @submit.prevent="sendFollowUp">
              <div class="flex items-center space-x-4 mb-4">
                <div class="flex-1">
                  <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Template de relance
                  </label>
                  <select
                    id="template_id"
                    v-model="followUpForm.template_id"
                    required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">Choisir un template</option>
                    <option v-for="template in templates" :key="template.id" :value="template.id">
                      {{ template.name }}
                    </option>
                  </select>
                </div>
                <div class="flex items-end">
                  <button
                    type="submit"
                    :disabled="selectedEmails.length === 0 || !followUpForm.template_id || processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Relancer ({{ selectedEmails.length }})
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Emails non ouverts -->
        <div v-if="emailsNotOpened.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">
                ❌ Emails non ouverts ({{ emailsNotOpened.length }})
              </h3>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  @change="toggleAllNotOpened"
                  :checked="allNotOpenedSelected"
                  class="form-checkbox text-blue-600 h-4 w-4 mr-2"
                />
                Tout sélectionner
              </label>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Sélection
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Envoyé le
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="emailSend in emailsNotOpened" :key="emailSend.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      :value="emailSend.id"
                      v-model="selectedEmails"
                      class="form-checkbox text-blue-600 h-4 w-4"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ emailSend.contact?.business_name || 'Contact inconnu' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ emailSend.contact?.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(emailSend.sent_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                      Non ouvert
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Emails ouverts -->
        <div v-if="emailsOpened.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">
                ✅ Emails ouverts ({{ emailsOpened.length }})
              </h3>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  @change="toggleAllOpened"
                  :checked="allOpenedSelected"
                  class="form-checkbox text-blue-600 h-4 w-4 mr-2"
                />
                Tout sélectionner
              </label>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Sélection
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Envoyé le
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ouvert le
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="emailSend in emailsOpened" :key="emailSend.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      :value="emailSend.id"
                      v-model="selectedEmails"
                      class="form-checkbox text-blue-600 h-4 w-4"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ emailSend.contact?.business_name || 'Contact inconnu' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ emailSend.contact?.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(emailSend.sent_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-green-600">{{ formatDate(emailSend.opened_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                      {{ emailSend.clicked_at ? 'Cliqué' : 'Ouvert' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Message si pas d'emails -->
        <div v-if="emailsNotOpened.length === 0 && emailsOpened.length === 0" class="bg-white shadow-sm rounded-lg p-12 text-center">
          <div class="text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Aucun email envoyé</h3>
            <p class="text-gray-500">Cette campagne n'a pas encore envoyé d'emails.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

interface EmailSend {
  id: number
  contact: {
    business_name: string
    email: string
  }
  sent_at: string
  opened_at?: string
  clicked_at?: string
  status: string
}

interface Template {
  id: number
  name: string
  subject: string
}

interface Props {
  campaign: {
    id: number
    name: string
    is_test: boolean
  }
  stats: {
    total_sent: number
    total_opened: number
    total_clicked: number
    open_rate: number
    click_rate: number
  }
  emailsOpened: EmailSend[]
  emailsNotOpened: EmailSend[]
  templates: Template[]
}

const props = defineProps<Props>()

const selectedEmails = ref<number[]>([])
const processing = ref(false)

const followUpForm = useForm({
  template_id: '',
  email_send_ids: [] as number[]
})

const allNotOpenedSelected = computed(() => {
  return props.emailsNotOpened.length > 0 && 
         props.emailsNotOpened.every(email => selectedEmails.value.includes(email.id))
})

const allOpenedSelected = computed(() => {
  return props.emailsOpened.length > 0 && 
         props.emailsOpened.every(email => selectedEmails.value.includes(email.id))
})

const toggleAllNotOpened = () => {
  if (allNotOpenedSelected.value) {
    // Déselectionner tous les non ouverts
    selectedEmails.value = selectedEmails.value.filter(id => 
      !props.emailsNotOpened.find(email => email.id === id)
    )
  } else {
    // Sélectionner tous les non ouverts
    const notOpenedIds = props.emailsNotOpened.map(email => email.id)
    selectedEmails.value = [...new Set([...selectedEmails.value, ...notOpenedIds])]
  }
}

const toggleAllOpened = () => {
  if (allOpenedSelected.value) {
    // Déselectionner tous les ouverts
    selectedEmails.value = selectedEmails.value.filter(id => 
      !props.emailsOpened.find(email => email.id === id)
    )
  } else {
    // Sélectionner tous les ouverts
    const openedIds = props.emailsOpened.map(email => email.id)
    selectedEmails.value = [...new Set([...selectedEmails.value, ...openedIds])]
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const sendFollowUp = () => {
  if (selectedEmails.value.length === 0) {
    alert('Veuillez sélectionner au moins un contact à relancer.')
    return
  }

  if (!followUpForm.template_id) {
    alert('Veuillez sélectionner un template de relance.')
    return
  }

  const template = props.templates.find(t => t.id === parseInt(followUpForm.template_id))
  const confirmMessage = `Envoyer une relance avec le template "${template?.name}" à ${selectedEmails.value.length} contact(s) ?`
  
  if (!confirm(confirmMessage)) return

  processing.value = true
  followUpForm.email_send_ids = selectedEmails.value

  followUpForm.post(route('email-reports.send-schedule-follow-up'), {
    onSuccess: () => {
      selectedEmails.value = []
      followUpForm.reset()
      processing.value = false
    },
    onError: () => {
      processing.value = false
    }
  })
}
</script>