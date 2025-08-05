<template>
  <AppLayout :title="`${seoQuery.name} - Résultats SEO`">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ seoQuery.name }}
        </h2>
        <div class="flex items-center space-x-3">
          <button 
            @click="analyzeNow"
            :disabled="!seoQuery.is_active"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 transition-colors"
          >
            Analyser maintenant
          </button>
          <Link 
            :href="route('seo-queries.edit', seoQuery.id)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Modifier
          </Link>
          <Link 
            :href="route('seo-queries.index')" 
            class="text-gray-600 hover:text-gray-900"
          >
            ← Retour
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
      <!-- Informations sur la requête -->
      <div class="mb-6 bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-4 mb-4 text-sm text-gray-600">
          <span class="font-medium">"{{ seoQuery.query }}"</span>
          <span v-if="seoQuery.location" class="flex items-center">
            📍 {{ seoQuery.location }}
          </span>
          <span 
            :class="seoQuery.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
          >
            {{ seoQuery.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <!-- Description -->
        <div v-if="seoQuery.description" class="mb-4 p-3 bg-gray-50 rounded">
          <p class="text-sm text-gray-700">{{ seoQuery.description }}</p>
        </div>

        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ stats?.total_contacts_tracked || 0 }}</div>
            <div class="text-sm text-blue-600">Sites suivis</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ stats?.currently_ranking || 0 }}</div>
            <div class="text-sm text-green-600">Sites classés</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600">
              {{ stats?.average_position ? Math.round(stats.average_position) : '-' }}
            </div>
            <div class="text-sm text-yellow-600">Position moyenne</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 mb-1">Fréquence</div>
            <div class="font-semibold text-purple-800">{{ getFrequencyLabel(seoQuery.frequency) }}</div>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600 mb-1">Prochaine analyse</div>
            <div class="font-semibold text-gray-800">
              <span v-if="stats?.next_analysis">{{ formatDate(stats.next_analysis) }}</span>
              <span v-else class="text-gray-400">Non programmée</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Graphique d'évolution -->
      <div v-if="evolution.length > 0" class="mb-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Évolution des positions (30 derniers jours)</h2>
        <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
          <!-- Ici vous pourriez intégrer Chart.js ou une autre bibliothèque de graphiques -->
          <div class="text-center text-gray-500">
            <div class="text-sm">Graphique d'évolution</div>
            <div class="text-xs mt-1">{{ evolution.length }} points de données disponibles</div>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
          <div>
            Position moyenne: <strong>{{ Math.round(evolution.reduce((sum, item) => sum + item.avg_position, 0) / evolution.length) }}</strong>
          </div>
          <div>
            Analyses: <strong>{{ evolution.reduce((sum, item) => sum + item.total_results, 0) }}</strong>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="mb-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow p-6 text-white">
        <h2 class="text-lg font-semibold mb-4">Actions rapides</h2>
        <div class="flex flex-wrap gap-3">
          <Link 
            :href="route('seo-queries.results', seoQuery.id)"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            Voir tous les résultats
          </Link>
          <button 
            @click="exportResults"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            Exporter les données
          </button>
          <button 
            @click="openCompareModal"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            Comparer avec d'autres requêtes
          </button>
        </div>
      </div>

      <!-- Résultats récents par contact -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Résultats récents par contact</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Contact
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Position actuelle
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Évolution
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Dernière analyse
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(results, contactId) in recentResults" :key="contactId" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ results[0].contact.business_name }}</div>
                    <div class="text-sm text-gray-500">
                      <a :href="results[0].contact.website" target="_blank" class="text-blue-600 hover:text-blue-800">
                        {{ results[0].contact.website }}
                      </a>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="results[0].found" class="flex items-center">
                    <span 
                      :class="getPositionColor(results[0].position)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      Position {{ results[0].position }}
                    </span>
                  </div>
                  <div v-else>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                      Non trouvé
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <span 
                      v-if="results[0].position_evolution"
                      :class="getEvolutionColor(results[0].position_evolution.status)"
                      class="text-sm font-medium"
                    >
                      {{ getEvolutionText(results[0].position_evolution) }}
                    </span>
                    <span v-else class="text-sm text-gray-400">-</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ formatDate(results[0].analyzed_at) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium">
                  <button 
                    @click="analyzeContact(results[0].contact)"
                    class="text-green-600 hover:text-green-900 mr-3"
                  >
                    Re-analyser
                  </button>
                  <button 
                    @click="showContactHistory(results[0].contact)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Historique
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="Object.keys(recentResults).length === 0" class="px-6 py-12 text-center">
          <div class="text-gray-500">
            <div class="text-lg mb-2">Aucun résultat disponible</div>
            <div class="text-sm">Lancez une première analyse pour voir les résultats</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal historique contact -->
    <div v-if="showHistoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Historique - {{ selectedContact?.business_name }}</h3>
          <button @click="showHistoryModal = false" class="text-gray-400 hover:text-gray-600">
            ✕
          </button>
        </div>
        
        <div class="space-y-3">
          <!-- Ici vous pourriez afficher l'historique complet du contact -->
          <div class="text-sm text-gray-600">
            Historique des positions pour ce contact sur la requête "{{ seoQuery.query }}"
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
import { ref } from 'vue'

interface Contact {
  id: number
  business_name: string
  website: string
}

interface SeoResult {
  id: number
  position?: number
  found: boolean
  analyzed_at: string
  contact: Contact
  position_evolution?: {
    status: string
    change: number
  }
}

interface SeoQuery {
  id: number
  name: string
  query: string
  location?: string
  frequency: 'daily' | 'weekly' | 'monthly'
  is_active: boolean
  description?: string
}

interface Props {
  seoQuery: SeoQuery
  recentResults: Record<string, SeoResult[]>
  stats: {
    total_contacts_tracked: number
    currently_ranking: number
    average_position?: number
    last_analysis?: string
    next_analysis?: string
  }
  evolution: Array<{
    date: string
    avg_position: number
    total_results: number
  }>
}

const props = defineProps<Props>()

const showHistoryModal = ref(false)
const selectedContact = ref<Contact | null>(null)

const getFrequencyLabel = (frequency: string) => {
  const labels = {
    daily: 'Quotidienne',
    weekly: 'Hebdomadaire',
    monthly: 'Mensuelle'
  }
  return labels[frequency] || frequency
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

const getPositionColor = (position: number) => {
  if (position <= 3) return 'bg-green-100 text-green-800'
  if (position <= 10) return 'bg-yellow-100 text-yellow-800'
  if (position <= 20) return 'bg-orange-100 text-orange-800'
  return 'bg-red-100 text-red-800'
}

const getEvolutionColor = (status: string) => {
  switch (status) {
    case 'up': return 'text-green-600'
    case 'down': return 'text-red-600'
    case 'stable': return 'text-gray-600'
    case 'new': return 'text-blue-600'
    case 'entered': return 'text-green-600'
    case 'lost': return 'text-red-600'
    default: return 'text-gray-600'
  }
}

const getEvolutionText = (evolution: { status: string, change: number }) => {
  switch (evolution.status) {
    case 'up': return `↗ +${evolution.change}`
    case 'down': return `↘ -${evolution.change}`
    case 'stable': return '→ Stable'
    case 'new': return '✨ Nouveau'
    case 'entered': return '🎯 Entré'
    case 'lost': return '❌ Perdu'
    default: return '-'
  }
}

const analyzeNow = () => {
  if (!confirm('Lancer l\'analyse SEO maintenant ?')) return
  
  router.post(route('seo-queries.analyze', props.seoQuery.id))
}

const analyzeContact = (contact: Contact) => {
  if (!confirm(`Re-analyser ${contact.business_name} ?`)) return
  
  // Cette route devra être ajoutée pour analyser un contact spécifique
  router.post(route('seo-queries.analyze-contact', { query: props.seoQuery.id, contact: contact.id }))
}

const showContactHistory = (contact: Contact) => {
  selectedContact.value = contact
  showHistoryModal.value = true
}

const exportResults = () => {
  // Implémentation de l'export
  window.open(route('seo-queries.export', props.seoQuery.id))
}

const openCompareModal = () => {
  // Rediriger vers la page de comparaison avec cette requête pré-sélectionnée
  router.get(route('seo-queries.compare', { query_ids: [props.seoQuery.id] }))
}
</script>