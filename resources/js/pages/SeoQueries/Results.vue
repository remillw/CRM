<template>
  <AppLayout :title="`Résultats - ${seoQuery.name}`">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Résultats détaillés - {{ seoQuery.name }}
          </h2>
          <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
            <span>"{{ seoQuery.query }}"</span>
            <span v-if="seoQuery.location" class="flex items-center">
              📍 {{ seoQuery.location }}
            </span>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <Link 
            :href="route('seo-queries.show', seoQuery.id)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Vue d'ensemble
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
      <!-- Statistiques rapides -->
      <div class="mb-6 bg-white rounded-lg shadow p-6">

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-3 rounded-lg">
            <div class="text-xl font-bold text-blue-600">{{ results.total }}</div>
            <div class="text-sm text-blue-600">Total résultats</div>
          </div>
          <div class="bg-green-50 p-3 rounded-lg">
            <div class="text-xl font-bold text-green-600">{{ foundResults }}</div>
            <div class="text-sm text-green-600">Sites trouvés</div>
          </div>
          <div class="bg-yellow-50 p-3 rounded-lg">
            <div class="text-xl font-bold text-yellow-600">{{ topResults }}</div>
            <div class="text-sm text-yellow-600">Top 10</div>
          </div>
          <div class="bg-purple-50 p-3 rounded-lg">
            <div class="text-xl font-bold text-purple-600">{{ averagePosition }}</div>
            <div class="text-sm text-purple-600">Position moyenne</div>
          </div>
        </div>
      </div>

      <!-- Filtres -->
      <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
              <select v-model="filterFound" @change="applyFilters" class="rounded border-gray-300 text-sm">
                <option value="">Tous</option>
                <option value="1">Trouvés seulement</option>
                <option value="0">Non trouvés seulement</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
              <select v-model="filterPosition" @change="applyFilters" class="rounded border-gray-300 text-sm">
                <option value="">Toutes positions</option>
                <option value="1-3">Top 3 (1-3)</option>
                <option value="1-10">Top 10 (1-10)</option>
                <option value="11-20">11-20</option>
                <option value="21-50">21-50</option>
                <option value="50+">50+</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
              <select v-model="filterPeriod" @change="applyFilters" class="rounded border-gray-300 text-sm">
                <option value="">Toute période</option>
                <option value="7">7 derniers jours</option>
                <option value="30">30 derniers jours</option>
                <option value="90">90 derniers jours</option>
              </select>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <button 
              @click="exportResults"
              class="px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 transition-colors"
            >
              Exporter
            </button>
            <button 
              @click="refreshResults"
              class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors"
            >
              Actualiser
            </button>
          </div>
        </div>
      </div>

      <!-- Tableau des résultats -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                    @click="sortBy('contact.business_name')">
                  <div class="flex items-center space-x-1">
                    <span>Contact</span>
                    <span v-if="sortField === 'contact.business_name'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                    @click="sortBy('position')">
                  <div class="flex items-center space-x-1">
                    <span>Position</span>
                    <span v-if="sortField === 'position'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Évolution
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  URL trouvée
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                    @click="sortBy('analyzed_at')">
                  <div class="flex items-center space-x-1">
                    <span>Analysé le</span>
                    <span v-if="sortField === 'analyzed_at'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="result in results.data" :key="result.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ result.contact.business_name }}</div>
                    <div class="text-sm text-gray-500">
                      <a :href="result.contact.website" target="_blank" class="text-blue-600 hover:text-blue-800">
                        {{ result.contact.website }}
                      </a>
                    </div>
                    <div v-if="result.contact.city" class="text-xs text-gray-400">
                      {{ result.contact.city }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="result.found" class="flex items-center space-x-2">
                    <span 
                      :class="getPositionColor(result.position)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ result.position }}
                    </span>
                    <span v-if="result.position <= 3" class="text-yellow-500">🏆</span>
                    <span v-else-if="result.position <= 10" class="text-gray-500">🥉</span>
                  </div>
                  <div v-else>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                      Non trouvé
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="result.position_evolution" class="flex items-center">
                    <span 
                      :class="getEvolutionColor(result.position_evolution.status)"
                      class="text-sm font-medium"
                    >
                      {{ getEvolutionText(result.position_evolution) }}
                    </span>
                  </div>
                  <div v-else class="text-sm text-gray-400">-</div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="result.url_found" class="text-sm">
                    <a :href="result.url_found" target="_blank" class="text-blue-600 hover:text-blue-800 truncate">
                      {{ truncateUrl(result.url_found) }}
                    </a>
                  </div>
                  <div v-else class="text-sm text-gray-400">-</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <div>{{ formatDate(result.analyzed_at) }}</div>
                  <div class="text-xs text-gray-400">{{ formatTime(result.analyzed_at) }}</div>
                </td>
                <td class="px-6 py-4 text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button 
                      @click="viewCompetitors(result)"
                      v-if="result.competitors && result.competitors.length > 0"
                      class="text-purple-600 hover:text-purple-900"
                    >
                      Concurrents
                    </button>
                    <button 
                      @click="viewDetails(result)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Détails
                    </button>
                    <button 
                      @click="reanalyze(result)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Re-analyser
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Message si pas de résultats -->
        <div v-if="results.data.length === 0" class="px-6 py-12 text-center">
          <div class="text-gray-500">
            <div class="text-lg mb-2">Aucun résultat trouvé</div>
            <div class="text-sm">Essayez de modifier les filtres ou lancez une nouvelle analyse</div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="results.links && results.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ results.from }} à {{ results.to }} sur {{ results.total }} résultats
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="link in results.links"
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

    <!-- Modal Concurrents -->
    <div v-if="showCompetitorsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">
            Concurrents - {{ selectedResult?.contact.business_name }}
          </h3>
          <button @click="showCompetitorsModal = false" class="text-gray-400 hover:text-gray-600">
            ✕
          </button>
        </div>
        
        <div v-if="selectedResult?.competitors" class="space-y-3">
          <div 
            v-for="(competitor, index) in selectedResult.competitors.slice(0, 10)" 
            :key="index"
            class="flex items-center justify-between p-3 border rounded"
          >
            <div class="flex items-center space-x-3">
              <div 
                :class="getPositionColor(competitor.position)"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ competitor.position }}
              </div>
              <div>
                <div class="font-medium">{{ competitor.title }}</div>
                <div class="text-sm text-gray-500 truncate max-w-md">{{ competitor.snippet }}</div>
              </div>
            </div>
            <a 
              :href="competitor.link" 
              target="_blank" 
              class="text-blue-600 hover:text-blue-800 text-sm"
            >
              Voir
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Détails de l'analyse</h3>
          <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600">
            ✕
          </button>
        </div>
        
        <div v-if="selectedResult" class="space-y-4">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <strong>Contact:</strong> {{ selectedResult.contact.business_name }}
            </div>
            <div>
              <strong>Site web:</strong> 
              <a :href="selectedResult.contact.website" target="_blank" class="text-blue-600">
                {{ selectedResult.contact.website }}
              </a>
            </div>
            <div>
              <strong>Requête:</strong> "{{ selectedResult.query_used }}"
            </div>
            <div>
              <strong>Position:</strong> {{ selectedResult.position || 'Non trouvé' }}
            </div>
            <div>
              <strong>Analysé le:</strong> {{ formatDate(selectedResult.analyzed_at) }}
            </div>
            <div>
              <strong>Résultats totaux:</strong> {{ selectedResult.total_results?.toLocaleString() }}
            </div>
          </div>
          
          <div v-if="selectedResult.serp_data" class="mt-4 p-3 bg-gray-50 rounded">
            <strong class="block mb-2">Données SERP:</strong>
            <pre class="text-xs text-gray-600 overflow-x-auto">{{ JSON.stringify(selectedResult.serp_data, null, 2) }}</pre>
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
import { ref, computed } from 'vue'

interface Contact {
  id: number
  business_name: string
  website: string
  city?: string
}

interface SeoResult {
  id: number
  position?: number
  found: boolean
  analyzed_at: string
  url_found?: string
  query_used: string
  total_results?: number
  competitors?: Array<{
    position: number
    title: string
    link: string
    snippet: string
  }>
  serp_data?: any
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
}

interface Props {
  seoQuery: SeoQuery
  results: {
    data: SeoResult[]
    links?: any[]
    from: number
    to: number
    total: number
  }
  filters: {
    filter?: string
    sort?: string
  }
}

const props = defineProps<Props>()

const filterFound = ref(props.filters.filter || '')
const filterPosition = ref('')
const filterPeriod = ref('')
const sortField = ref('analyzed_at')
const sortDirection = ref('desc')
const showCompetitorsModal = ref(false)
const showDetailsModal = ref(false)
const selectedResult = ref<SeoResult | null>(null)

const foundResults = computed(() => {
  return props.results.data.filter(result => result.found).length
})

const topResults = computed(() => {
  return props.results.data.filter(result => result.found && result.position && result.position <= 10).length
})

const averagePosition = computed(() => {
  const positions = props.results.data
    .filter(result => result.found && result.position)
    .map(result => result.position!)
  
  if (positions.length === 0) return '-'
  
  const avg = positions.reduce((sum, pos) => sum + pos, 0) / positions.length
  return Math.round(avg)
})

const getPositionColor = (position?: number) => {
  if (!position) return 'bg-gray-100 text-gray-800'
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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatTime = (dateString: string) => {
  return new Date(dateString).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const truncateUrl = (url: string) => {
  if (url.length <= 60) return url
  return url.substring(0, 57) + '...'
}

const applyFilters = () => {
  const params = new URLSearchParams()
  if (filterFound.value) params.append('filter[found]', filterFound.value)
  if (filterPosition.value) params.append('filter[position]', filterPosition.value)
  if (filterPeriod.value) params.append('filter[period]', filterPeriod.value)
  
  router.get(route('seo-queries.results', props.seoQuery.id), Object.fromEntries(params), {
    preserveState: true,
    preserveScroll: true
  })
}

const sortBy = (field: string) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  
  const params = new URLSearchParams()
  params.append('sort', `${sortDirection.value === 'desc' ? '-' : ''}${field}`)
  
  router.get(route('seo-queries.results', props.seoQuery.id), Object.fromEntries(params), {
    preserveState: true,
    preserveScroll: true
  })
}

const viewCompetitors = (result: SeoResult) => {
  selectedResult.value = result
  showCompetitorsModal.value = true
}

const viewDetails = (result: SeoResult) => {
  selectedResult.value = result
  showDetailsModal.value = true
}

const reanalyze = (result: SeoResult) => {
  if (!confirm(`Re-analyser ${result.contact.business_name} ?`)) return
  
  router.post(route('seo-queries.analyze-contact', { 
    query: props.seoQuery.id, 
    contact: result.contact.id 
  }))
}

const exportResults = () => {
  const params = new URLSearchParams(window.location.search)
  window.open(route('seo-queries.export-results', props.seoQuery.id) + '?' + params.toString())
}

const refreshResults = () => {
  router.reload()
}
</script>