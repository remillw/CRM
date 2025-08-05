<template>
  <AppLayout title="Modifier la requête SEO">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Modifier la requête SEO
        </h2>
        <div class="flex items-center space-x-4">
          <Link 
            :href="route('seo-queries.show', seoQuery.id)" 
            class="text-blue-600 hover:text-blue-900"
          >
            Voir les résultats
          </Link>
          <Link 
            :href="route('seo-queries.index')" 
            class="text-gray-600 hover:text-gray-900"
          >
            ← Retour à la liste
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg">

        <form @submit.prevent="submit" class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informations de base -->
            <div class="space-y-6">
              <h2 class="text-lg font-medium text-gray-900 border-b pb-2">Informations de base</h2>
              
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                  Nom de la requête *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Ex: Pizzeria Marseille - Quartier Vieux Port"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-300': form.errors.name }"
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
              </div>

              <div>
                <label for="query" class="block text-sm font-medium text-gray-700 mb-1">
                  Requête de recherche *
                </label>
                <input
                  id="query"
                  v-model="form.query"
                  type="text"
                  placeholder="Ex: pizzeria marseille vieux port"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-300': form.errors.query }"
                />
                <div v-if="form.errors.query" class="mt-1 text-sm text-red-600">{{ form.errors.query }}</div>
                <div class="mt-1 text-sm text-gray-500">
                  ⚠️ Modifier la requête effacera l'historique des résultats
                </div>
              </div>

              <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                  Localisation (optionnel)
                </label>
                <input
                  id="location"
                  v-model="form.location"
                  type="text"
                  placeholder="Ex: Marseille, France"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-300': form.errors.location }"
                />
                <div v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</div>
              </div>

              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                  Description (optionnel)
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  placeholder="Décrivez l'objectif de cette requête SEO..."
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-300': form.errors.description }"
                />
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
              </div>
            </div>

            <!-- Configuration -->
            <div class="space-y-6">
              <h2 class="text-lg font-medium text-gray-900 border-b pb-2">Configuration</h2>

              <div>
                <label for="frequency" class="block text-sm font-medium text-gray-700 mb-1">
                  Fréquence d'analyse *
                </label>
                <select
                  id="frequency"
                  v-model="form.frequency"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-300': form.errors.frequency }"
                >
                  <option value="one-time">🎯 Une seule fois</option>
                  <option value="daily">Quotidienne</option>
                  <option value="weekly">Hebdomadaire (recommandé)</option>
                  <option value="monthly">Mensuelle</option>
                </select>
                <div v-if="form.errors.frequency" class="mt-1 text-sm text-red-600">{{ form.errors.frequency }}</div>
                <div class="mt-1 text-sm text-gray-500">
                  <span v-if="form.frequency === 'one-time'">
                    ⚡ L'analyse se fera une seule fois, idéal pour des analyses ponctuelles
                  </span>
                  <span v-else>
                    🔄 Modifier la fréquence reprogrammera la prochaine analyse
                  </span>
                </div>
                <div v-if="seoQuery.is_one_time && seoQuery.executed_at" class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                  ⚠️ Cette requête ponctuelle a déjà été exécutée le {{ formatDate(seoQuery.executed_at) }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Campagnes ciblées (optionnel)
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto border rounded-md p-3">
                  <div v-for="campaign in campaigns" :key="campaign.id" class="flex items-center">
                    <input
                      :id="`campaign-${campaign.id}`"
                      type="checkbox"
                      :value="campaign.id"
                      v-model="form.target_campaigns"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label :for="`campaign-${campaign.id}`" class="ml-3 text-sm">
                      <span class="font-medium">{{ campaign.name }}</span>
                      <span class="text-gray-500">• {{ campaign.activity_type }} • {{ campaign.city }}</span>
                    </label>
                  </div>
                </div>
                <div v-if="form.errors.target_campaigns" class="mt-1 text-sm text-red-600">{{ form.errors.target_campaigns }}</div>
              </div>

              <div class="flex items-center">
                <input
                  id="is_active"
                  type="checkbox"
                  v-model="form.is_active"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                  Requête active
                </label>
              </div>

              <!-- Statistiques -->
              <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Statistiques actuelles</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <div class="text-gray-500">Créée le</div>
                    <div class="font-medium">{{ formatDate(seoQuery.created_at) }}</div>
                  </div>
                  <div>
                    <div class="text-gray-500">Dernière analyse</div>
                    <div class="font-medium">
                      <span v-if="seoQuery.last_analyzed_at">{{ formatDate(seoQuery.last_analyzed_at) }}</span>
                      <span v-else class="text-gray-400">Jamais</span>
                    </div>
                  </div>
                  <div>
                    <div class="text-gray-500">Prochaine analyse</div>
                    <div class="font-medium">
                      <span v-if="seoQuery.next_analysis_at">{{ formatDate(seoQuery.next_analysis_at) }}</span>
                      <span v-else class="text-gray-400">Non programmée</span>
                    </div>
                  </div>
                  <div>
                    <div class="text-gray-500">Résultats stockés</div>
                    <div class="font-medium">{{ seoQuery.results_count || 0 }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-8 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                type="button"
                @click="analyzeNow"
                :disabled="!form.is_active || form.processing || (seoQuery.is_one_time && seoQuery.executed_at)"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ seoQuery.is_one_time && seoQuery.executed_at ? 'Déjà exécutée' : 'Analyser maintenant' }}
              </button>
              <button
                v-if="seoQuery.is_one_time && seoQuery.executed_at"
                type="button"
                @click="relaunchQuery"
                :disabled="form.processing"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Relancer
              </button>
              <button
                type="button"
                @click="deleteQuery"
                :disabled="form.processing"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Supprimer
              </button>
            </div>
            
            <div class="flex items-center space-x-4">
              <Link 
                :href="route('seo-queries.show', seoQuery.id)" 
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
              >
                Annuler
              </Link>
              <button 
                type="submit" 
                :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="form.processing">Sauvegarde...</span>
                <span v-else>Sauvegarder</span>
              </button>
            </div>
          </div>
        </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Campaign {
  id: number
  name: string
  activity_type: string
  city: string
}

interface SeoQuery {
  id: number
  name: string
  query: string
  location?: string
  frequency: 'daily' | 'weekly' | 'monthly'
  is_active: boolean
  is_one_time: boolean
  executed_at?: string
  target_campaigns?: number[]
  description?: string
  created_at: string
  last_analyzed_at?: string
  next_analysis_at?: string
  results_count?: number
}

interface Props {
  seoQuery: SeoQuery
  campaigns: Campaign[]
}

const props = defineProps<Props>()

const form = useForm({
  name: props.seoQuery.name,
  query: props.seoQuery.query,
  location: props.seoQuery.location || '',
  frequency: props.seoQuery.is_one_time ? 'one-time' : props.seoQuery.frequency,
  target_campaigns: props.seoQuery.target_campaigns || [],
  description: props.seoQuery.description || '',
  is_active: props.seoQuery.is_active,
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const submit = () => {
  form.put(route('seo-queries.update', props.seoQuery.id), {
    onSuccess: () => {
      // Redirection gérée par le contrôleur
    }
  })
}

const analyzeNow = () => {
  if (!confirm('Lancer l\'analyse SEO maintenant ?')) return
  
  router.post(route('seo-queries.analyze', props.seoQuery.id), {}, {
    onSuccess: () => {
      // Message de succès affiché
    }
  })
}

const relaunchQuery = () => {
  if (!confirm(`Relancer la requête "${props.seoQuery.name}" ?\n\nCela va réinitialiser la requête ponctuelle et la lancer à nouveau.`)) return
  
  router.post(route('seo-queries.relaunch', props.seoQuery.id), {}, {
    onSuccess: () => {
      // Le message de succès sera affiché par Laravel
    }
  })
}

const deleteQuery = () => {
  const resultsCount = props.seoQuery.results_count || 0
  const message = resultsCount > 0 
    ? `Êtes-vous sûr de vouloir supprimer cette requête SEO ?\n\n⚠️ ${resultsCount} résultats SEO associés seront également supprimés définitivement.`
    : 'Êtes-vous sûr de vouloir supprimer cette requête SEO ?'
    
  if (!confirm(message)) return
  
  router.delete(route('seo-queries.destroy', props.seoQuery.id), {
    onSuccess: () => {
      // Redirection vers l'index
    }
  })
}
</script>