<template>
  <AppLayout title="Requêtes SEO">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Requêtes SEO Personnalisées
        </h2>
        <Link 
          :href="route('seo-queries.create')" 
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
        >
          <Plus class="w-4 h-4 mr-2" />
          Nouvelle requête
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
      <!-- Statistiques -->
      <div class="mb-6 bg-white rounded-lg shadow p-6">

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ stats.total_queries }}</div>
            <div class="text-sm text-blue-600">Requêtes totales</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ stats.active_queries }}</div>
            <div class="text-sm text-green-600">Requêtes actives</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600">{{ stats.due_for_analysis }}</div>
            <div class="text-sm text-yellow-600">En attente d'analyse</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-2xl font-bold text-purple-600">{{ stats.total_results }}</div>
            <div class="text-sm text-purple-600">Résultats stockés</div>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="mb-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow p-6 text-white">
        <h2 class="text-lg font-semibold mb-4">Actions rapides</h2>
        <div class="flex flex-wrap gap-3">
          <Link
            :href="route('seo-queries.create')"
            class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 rounded-lg transition-colors font-medium shadow-lg"
          >
            ✨ Créer une nouvelle requête SEO
          </Link>
          <div class="flex items-center space-x-2">
            <span class="text-sm text-white/80">Templates rapides :</span>
            <button 
              @click="createQuickTemplate('restaurant')"
              class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded text-sm transition-colors"
            >
              🍽️ Restaurant
            </button>
            <button 
              @click="createQuickTemplate('coiffeur')"
              class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded text-sm transition-colors"
            >
              ✂️ Coiffeur
            </button>
            <button 
              @click="createQuickTemplate('plombier')"
              class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded text-sm transition-colors"
            >
              🔧 Plombier
            </button>
            <button 
              @click="createQuickTemplate('one-time')"
              class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded text-sm transition-colors"
            >
              🎯 Analyse ponctuelle
            </button>
          </div>
        </div>
        <div class="flex flex-wrap gap-3 mt-3">
          <button 
            @click="analyzeAllDue"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            🚀 Analyser toutes les requêtes en attente
          </button>
          <button 
            @click="openCompareModal"
            class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
          >
            📊 Comparer les requêtes
          </button>
        </div>
      </div>

      <!-- Liste des requêtes -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Mes requêtes SEO</h2>
            <div class="flex items-center space-x-2">
              <select v-model="filterStatus" @change="applyFilters" class="rounded border-gray-300 text-sm">
                <option value="">Tous les statuts</option>
                <option value="active">Actives</option>
                <option value="inactive">Inactives</option>
              </select>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Requête
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fréquence
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Résultats
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
              <tr v-for="query in queries.data" :key="query.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ query.name }}</div>
                    <div class="text-sm text-gray-500">{{ query.query }}</div>
                    <div v-if="query.location" class="text-xs text-gray-400">📍 {{ query.location }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span 
                    :class="query.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ query.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ getFrequencyLabel(query) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ query.results_count }} résultats
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <span v-if="query.last_analyzed_at">
                    {{ formatDate(query.last_analyzed_at) }}
                  </span>
                  <span v-else class="text-gray-400">Jamais</span>
                </td>
                <td class="px-6 py-4 text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <Link 
                      :href="route('seo-queries.show', query.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Voir
                    </Link>
                    <button 
                      @click="analyzeQuery(query)"
                      :disabled="!query.is_active || (query.is_one_time && query.executed_at)"
                      class="text-green-600 hover:text-green-900 disabled:text-gray-400"
                    >
                      {{ query.is_one_time && query.executed_at ? 'Déjà exécutée' : 'Analyser' }}
                    </button>
                    <button 
                      v-if="query.is_one_time && query.executed_at"
                      @click="relaunchQuery(query)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Relancer cette requête ponctuelle"
                    >
                      Relancer
                    </button>
                    <button 
                      @click="toggleQuery(query)"
                      :class="query.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                    >
                      {{ query.is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                    <Link 
                      :href="route('seo-queries.edit', query.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Modifier
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Message si pas de requêtes -->
        <div v-if="queries.data.length === 0" class="px-6 py-12 text-center">
          <div class="text-gray-500">
            <div class="text-6xl mb-4">🔍</div>
            <div class="text-xl mb-2">Aucune requête SEO trouvée</div>
            <div class="text-sm mb-8">Créez votre première requête SEO pour analyser le positionnement de vos contacts sur Google</div>
            <div class="space-y-4">
              <Link
                :href="route('seo-queries.create')"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-lg"
              >
                <Plus class="w-5 h-5 mr-2" />
                Créer ma première requête SEO
              </Link>
              <div class="text-xs text-gray-400 max-w-md mx-auto">
                💡 Une requête SEO vous permet de suivre la position de vos contacts sur Google pour des mots-clés spécifiques comme "pizzeria marseille" ou "coiffeur lyon"
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="queries.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ queries.from }} à {{ queries.to }} sur {{ queries.total }} requêtes
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="link in queries.links"
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

    <!-- Modal de comparaison -->
    <div v-if="showCompareModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Comparer les requêtes</h3>
        <div class="space-y-3 mb-4">
          <div v-for="query in queries.data" :key="query.id" class="flex items-center">
            <input 
              type="checkbox" 
              :id="`compare-${query.id}`"
              v-model="selectedForCompare"
              :value="query.id"
              class="rounded border-gray-300"
            />
            <label :for="`compare-${query.id}`" class="ml-2 text-sm">{{ query.name }}</label>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Période</label>
          <select v-model="compareDateRange" class="w-full rounded border-gray-300">
            <option value="7">7 derniers jours</option>
            <option value="30">30 derniers jours</option>
            <option value="90">90 derniers jours</option>
          </select>
        </div>
        <div class="flex justify-end space-x-3">
          <button 
            @click="showCompareModal = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-800"
          >
            Annuler
          </button>
          <button 
            @click="compareQueries"
            :disabled="selectedForCompare.length < 2"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
          >
            Comparer
          </button>
        </div>
      </div>

      <!-- Section d'aide -->
      <div class="mt-6 bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">💡 Comment utiliser les requêtes SEO ?</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="font-semibold text-blue-600 mb-2">1. Créer une requête</div>
            <div>Définissez les mots-clés que vos clients tapent dans Google pour trouver vos services.</div>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="font-semibold text-green-600 mb-2">2. Cibler vos contacts</div>
            <div>Choisissez quelles campagnes analyser ou laissez vide pour tous les contacts avec un site web.</div>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="font-semibold text-purple-600 mb-2">3. Suivre l'évolution</div>
            <div>Analysez automatiquement et comparez les positions dans le temps pour optimiser votre référencement.</div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Plus } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

interface SeoQuery {
  id: number
  name: string
  query: string
  location?: string
  frequency: 'daily' | 'weekly' | 'monthly'
  is_active: boolean
  is_one_time: boolean
  executed_at?: string
  results_count: number
  last_analyzed_at?: string
  next_analysis_at?: string
}

interface Props {
  queries: {
    data: SeoQuery[]
    links: any[]
    from: number
    to: number
    total: number
  }
  stats: {
    total_queries: number
    active_queries: number
    due_for_analysis: number
    total_results: number
  }
  filters: {
    filter?: string
    sort?: string
  }
}

const props = defineProps<Props>()

const filterStatus = ref(props.filters.filter || '')
const showCompareModal = ref(false)
const selectedForCompare = ref<number[]>([])
const compareDateRange = ref(30)

const getFrequencyLabel = (query: SeoQuery) => {
  if (query.is_one_time) {
    return query.executed_at ? '🎯 Exécutée' : '🎯 Une seule fois'
  }
  
  const labels = {
    daily: 'Quotidienne',
    weekly: 'Hebdomadaire', 
    monthly: 'Mensuelle'
  }
  return labels[query.frequency] || query.frequency
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

const applyFilters = () => {
  const params = new URLSearchParams()
  if (filterStatus.value) {
    params.append('filter[is_active]', filterStatus.value === 'active' ? '1' : '0')
  }
  
  router.get(route('seo-queries.index'), Object.fromEntries(params), {
    preserveState: true,
    preserveScroll: true
  })
}

const analyzeQuery = (query: SeoQuery) => {
  if (!confirm(`Lancer l'analyse SEO pour la requête "${query.name}" ?`)) return
  
  router.post(route('seo-queries.analyze', query.id), {}, {
    onSuccess: () => {
      // Le message de succès sera affiché par Laravel
    }
  })
}

const toggleQuery = (query: SeoQuery) => {
  const action = query.is_active ? 'désactiver' : 'activer'
  if (!confirm(`Voulez-vous ${action} la requête "${query.name}" ?`)) return
  
  router.post(route('seo-queries.toggle', query.id))
}

const relaunchQuery = (query: SeoQuery) => {
  if (!confirm(`Relancer la requête "${query.name}" ?\n\nCela va réinitialiser la requête ponctuelle et la lancer à nouveau.`)) return
  
  router.post(route('seo-queries.relaunch', query.id), {}, {
    onSuccess: () => {
      // Le message de succès sera affiché par Laravel
    }
  })
}

const analyzeAllDue = () => {
  if (!confirm('Lancer l\'analyse de toutes les requêtes en attente ?')) return
  
  // Cette route devra être ajoutée
  router.post(route('seo-queries.analyze-all'))
}

const openCompareModal = () => {
  showCompareModal.value = true
  selectedForCompare.value = []
}

const compareQueries = () => {
  if (selectedForCompare.value.length < 2) return
  
  const params = new URLSearchParams()
  selectedForCompare.value.forEach(id => params.append('query_ids[]', id.toString()))
  params.append('date_range', compareDateRange.value.toString())
  
  router.get(route('seo-queries.compare'), Object.fromEntries(params))
}

const createQuickTemplate = (type: string) => {
  const templates = {
    restaurant: {
      name: 'Restaurants locaux',
      query: 'restaurant',
      description: 'Template pour analyser le positionnement des restaurants locaux',
      frequency: 'weekly'
    },
    coiffeur: {
      name: 'Coiffeurs locaux', 
      query: 'coiffeur',
      description: 'Template pour analyser le positionnement des coiffeurs locaux',
      frequency: 'weekly'
    },
    plombier: {
      name: 'Plombiers locaux',
      query: 'plombier', 
      description: 'Template pour analyser le positionnement des plombiers locaux',
      frequency: 'weekly'
    },
    'one-time': {
      name: 'Analyse ponctuelle',
      query: '',
      description: 'Analyse SEO à usage unique pour une campagne spécifique',
      frequency: 'one-time'
    }
  }
  
  const template = templates[type]
  if (!template) return
  
  const params = new URLSearchParams()
  params.append('template_name', template.name)
  params.append('template_query', template.query)
  params.append('template_description', template.description)
  params.append('template_frequency', template.frequency)
  
  router.get(route('seo-queries.create'), Object.fromEntries(params))
}
</script>