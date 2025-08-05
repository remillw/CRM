<template>
  <AppLayout title="Comparaison des requêtes SEO">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Comparaison des requêtes SEO
        </h2>
        <Link 
          :href="route('seo-queries.index')" 
          class="text-gray-600 hover:text-gray-900"
        >
          ← Retour à la liste
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
      <!-- Informations -->
      <div class="mb-6 bg-white rounded-lg shadow p-6">

        <div class="flex items-center space-x-4 text-sm text-gray-600">
          <span>Période analysée: <strong>{{ dateRange }} derniers jours</strong></span>
          <span>Requêtes comparées: <strong>{{ comparisonData.length }}</strong></span>
        </div>
      </div>

      <!-- Résumé des requêtes comparées -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div 
          v-for="(data, index) in comparisonData" 
          :key="data.query.id"
          class="bg-white rounded-lg shadow p-4"
          :class="getQueryColor(index)"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-gray-900">{{ data.query.name }}</h3>
              <p class="text-sm text-gray-600">"{{ data.query.query }}"</p>
              <p v-if="data.query.location" class="text-xs text-gray-500">📍 {{ data.query.location }}</p>
            </div>
            <div 
              class="w-4 h-4 rounded-full"
              :class="getQueryDotColor(index)"
            ></div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <div class="text-gray-500">Position moyenne</div>
              <div class="font-bold text-lg">
                {{ data.average_position ? Math.round(data.average_position) : '-' }}
              </div>
            </div>
            <div>
              <div class="text-gray-500">Meilleure position</div>
              <div class="font-bold text-lg text-green-600">
                {{ data.best_position || '-' }}
              </div>
            </div>
            <div>
              <div class="text-gray-500">Résultats</div>
              <div class="font-semibold">{{ data.total_rankings }}</div>
            </div>
            <div>
              <div class="text-gray-500">Statut</div>
              <span 
                :class="data.query.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ data.query.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Graphique de comparaison -->
      <div class="mb-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Évolution comparée des positions</h2>
        <div class="h-80 bg-gray-50 rounded-lg flex items-center justify-center">
          <!-- Ici vous pourriez intégrer Chart.js ou une autre bibliothèque -->
          <div class="text-center text-gray-500">
            <div class="text-lg mb-2">Graphique de comparaison</div>
            <div class="text-sm">Intégration Chart.js recommandée</div>
            <div class="mt-4 text-xs">
              Données disponibles: {{ getTotalDataPoints() }} points
            </div>
          </div>
        </div>
        
        <!-- Légende -->
        <div class="mt-4 flex flex-wrap gap-4">
          <div 
            v-for="(data, index) in comparisonData" 
            :key="data.query.id"
            class="flex items-center space-x-2"
          >
            <div 
              class="w-3 h-3 rounded-full"
              :class="getQueryDotColor(index)"
            ></div>
            <span class="text-sm text-gray-700">{{ data.query.name }}</span>
          </div>
        </div>
      </div>

      <!-- Tableau de comparaison détaillé -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Analyse comparative détaillée</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Requête
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Position moyenne
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Meilleure position
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Sites classés
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Performance
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr 
                v-for="(data, index) in sortedComparisonData" 
                :key="data.query.id" 
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :class="getQueryDotColor(getOriginalIndex(data.query.id))"
                    ></div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ data.query.name }}</div>
                      <div class="text-sm text-gray-500">"{{ data.query.query }}"</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-lg font-semibold">
                    {{ data.average_position ? Math.round(data.average_position) : '-' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div 
                    class="text-lg font-bold"
                    :class="getPositionColor(data.best_position)"
                  >
                    {{ data.best_position || '-' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    <div class="font-semibold">{{ data.total_rankings }}</div>
                    <div class="text-gray-500">résultats</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-3">
                      <div 
                        class="h-2 rounded-full"
                        :class="getPerformanceColor(data.average_position)"
                        :style="{ width: getPerformanceWidth(data.average_position) + '%' }"
                      ></div>
                    </div>
                    <span class="text-sm text-gray-600">
                      {{ getPerformanceLabel(data.average_position) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <Link 
                      :href="route('seo-queries.show', data.query.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Détails
                    </Link>
                    <button 
                      @click="analyzeQuery(data.query)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Analyser
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Insights et recommandations -->
      <div class="mt-6 bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">📊 Insights et recommandations</h3>
        <div class="space-y-2 text-sm text-blue-800">
          <div v-if="bestPerformingQuery">
            <strong>Meilleure performance:</strong> 
            "{{ bestPerformingQuery.query.name }}" avec une position moyenne de {{ Math.round(bestPerformingQuery.average_position) }}
          </div>
          <div v-if="mostActiveQuery">
            <strong>Plus de résultats:</strong> 
            "{{ mostActiveQuery.query.name }}" avec {{ mostActiveQuery.total_rankings }} sites classés
          </div>
          <div v-if="needsAttentionQuery">
            <strong>Nécessite attention:</strong> 
            "{{ needsAttentionQuery.query.name }}" - position moyenne élevée ({{ Math.round(needsAttentionQuery.average_position) }})
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button 
            @click="exportComparison"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Exporter la comparaison
          </button>
          <button 
            @click="analyzeAllQueries"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            Analyser toutes les requêtes
          </button>
        </div>
        
        <div class="flex items-center space-x-2 text-sm text-gray-600">
          <span>Autre période:</span>
          <select @change="changeDateRange" class="rounded border-gray-300 text-sm">
            <option value="7" :selected="dateRange === 7">7 derniers jours</option>
            <option value="30" :selected="dateRange === 30">30 derniers jours</option>
            <option value="90" :selected="dateRange === 90">90 derniers jours</option>
          </select>
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed } from 'vue'

interface SeoQuery {
  id: number
  name: string
  query: string
  location?: string
  is_active: boolean
}

interface ComparisonData {
  query: SeoQuery
  results: any[]
  average_position?: number
  best_position?: number
  total_rankings: number
  evolution: Record<string, number>
}

interface Props {
  comparisonData: ComparisonData[]
  dateRange: number
}

const props = defineProps<Props>()

const queryColors = [
  'border-l-4 border-blue-500',
  'border-l-4 border-green-500', 
  'border-l-4 border-yellow-500',
  'border-l-4 border-purple-500',
  'border-l-4 border-pink-500'
]

const queryDotColors = [
  'bg-blue-500',
  'bg-green-500',
  'bg-yellow-500', 
  'bg-purple-500',
  'bg-pink-500'
]

const sortedComparisonData = computed(() => {
  return [...props.comparisonData].sort((a, b) => {
    if (!a.average_position && !b.average_position) return 0
    if (!a.average_position) return 1
    if (!b.average_position) return -1
    return a.average_position - b.average_position
  })
})

const bestPerformingQuery = computed(() => {
  return props.comparisonData
    .filter(data => data.average_position)
    .reduce((best, current) => 
      !best || (current.average_position && current.average_position < best.average_position) 
        ? current 
        : best, 
      null as ComparisonData | null
    )
})

const mostActiveQuery = computed(() => {
  return props.comparisonData.reduce((most, current) => 
    current.total_rankings > most.total_rankings ? current : most
  )
})

const needsAttentionQuery = computed(() => {
  return props.comparisonData
    .filter(data => data.average_position && data.average_position > 50)
    .reduce((worst, current) => 
      !worst || (current.average_position && current.average_position > worst.average_position)
        ? current 
        : worst, 
      null as ComparisonData | null
    )
})

const getQueryColor = (index: number) => {
  return queryColors[index % queryColors.length]
}

const getQueryDotColor = (index: number) => {
  return queryDotColors[index % queryDotColors.length]
}

const getOriginalIndex = (queryId: number) => {
  return props.comparisonData.findIndex(data => data.query.id === queryId)
}

const getTotalDataPoints = () => {
  return props.comparisonData.reduce((total, data) => {
    return total + Object.keys(data.evolution).length
  }, 0)
}

const getPositionColor = (position?: number) => {
  if (!position) return 'text-gray-400'
  if (position <= 3) return 'text-green-600'
  if (position <= 10) return 'text-yellow-600'
  if (position <= 20) return 'text-orange-600'
  return 'text-red-600'
}

const getPerformanceColor = (position?: number) => {
  if (!position) return 'bg-gray-300'
  if (position <= 3) return 'bg-green-500'
  if (position <= 10) return 'bg-yellow-500'
  if (position <= 20) return 'bg-orange-500'
  return 'bg-red-500'
}

const getPerformanceWidth = (position?: number) => {
  if (!position) return 0
  // Inverser pour que plus la position est bonne, plus la barre est large
  return Math.max(0, 100 - (position - 1) * 2)
}

const getPerformanceLabel = (position?: number) => {
  if (!position) return 'Aucune donnée'
  if (position <= 3) return 'Excellent'
  if (position <= 10) return 'Très bon'
  if (position <= 20) return 'Bon'
  if (position <= 50) return 'Moyen'
  return 'À améliorer'
}

const analyzeQuery = (query: SeoQuery) => {
  if (!confirm(`Lancer l'analyse pour "${query.name}" ?`)) return
  
  router.post(route('seo-queries.analyze', query.id))
}

const analyzeAllQueries = () => {
  if (!confirm('Lancer l\'analyse pour toutes les requêtes comparées ?')) return
  
  const queryIds = props.comparisonData.map(data => data.query.id)
  router.post(route('seo-queries.analyze-multiple'), { query_ids: queryIds })
}

const exportComparison = () => {
  const queryIds = props.comparisonData.map(data => data.query.id)
  const params = new URLSearchParams()
  queryIds.forEach(id => params.append('query_ids[]', id.toString()))
  params.append('date_range', props.dateRange.toString())
  
  window.open(route('seo-queries.export-comparison') + '?' + params.toString())
}

const changeDateRange = (event: Event) => {
  const newRange = (event.target as HTMLSelectElement).value
  const queryIds = props.comparisonData.map(data => data.query.id)
  
  const params = new URLSearchParams()
  queryIds.forEach(id => params.append('query_ids[]', id.toString()))
  params.append('date_range', newRange)
  
  router.get(route('seo-queries.compare'), Object.fromEntries(params))
}
</script>