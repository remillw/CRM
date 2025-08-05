<template>
  <AppLayout title="Nouvelle requête SEO">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Créer une nouvelle requête SEO
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
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8">
        <!-- Message template -->
        <div v-if="templateName" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center">
            <div class="text-blue-600 mr-3">ℹ️</div>
            <div>
              <div class="text-sm font-medium text-blue-800">Template appliqué</div>
              <div class="text-sm text-blue-600">Les champs ont été pré-remplis avec le template "{{ templateName }}". Vous pouvez les modifier selon vos besoins.</div>
            </div>
          </div>
        </div>
        
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
                  Utilisez des mots-clés précis que vos clients tapent dans Google
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
                <div class="mt-1 text-sm text-gray-500">
                  Affine les résultats pour une zone géographique spécifique
                </div>
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
                    🔄 L'analyse hebdomadaire est recommandée pour un bon suivi continu
                  </span>
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
                <div class="mt-1 text-sm text-gray-500">
                  Si aucune campagne n'est sélectionnée, tous les contacts avec un site web seront analysés
                </div>
              </div>

              <div class="flex items-center">
                <input
                  id="is_active"
                  type="checkbox"
                  v-model="form.is_active"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                  Activer la requête immédiatement
                </label>
              </div>
              <div class="mt-1 text-sm text-gray-500">
                Si activée, la première analyse sera programmée automatiquement
              </div>
            </div>
          </div>

          <!-- Aperçu -->
          <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Aperçu</h3>
            <div class="text-sm text-gray-600 space-y-1">
              <div v-if="form.name"><strong>Nom :</strong> {{ form.name }}</div>
              <div v-if="form.query"><strong>Requête :</strong> "{{ form.query }}"</div>
              <div v-if="form.location"><strong>Localisation :</strong> {{ form.location }}</div>
              <div><strong>Fréquence :</strong> {{ getFrequencyLabel(form.frequency) }}</div>
              <div><strong>Statut :</strong> {{ form.is_active ? 'Active' : 'Inactive' }}</div>
              <div v-if="form.target_campaigns?.length">
                <strong>{{ form.target_campaigns.length }} campagne(s) ciblée(s)</strong>
              </div>
              <div v-else>
                <strong>Toutes les campagnes</strong> (contacts avec site web)
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-8 flex items-center justify-end space-x-4">
            <Link 
              :href="route('seo-queries.index')" 
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
            >
              Annuler
            </Link>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="form.processing">Création...</span>
              <span v-else>Créer la requête</span>
            </button>
          </div>
        </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Campaign {
  id: number
  name: string
  activity_type: string
  city: string
}

interface Props {
  campaigns: Campaign[]
}

const props = defineProps<Props>()

// Récupérer les paramètres du template si présents
const urlParams = new URLSearchParams(window.location.search)
const templateName = urlParams.get('template_name') || ''
const templateQuery = urlParams.get('template_query') || ''
const templateDescription = urlParams.get('template_description') || ''
const templateFrequency = urlParams.get('template_frequency') || 'weekly'

const form = useForm({
  name: templateName,
  query: templateQuery,
  location: '',
  frequency: templateFrequency,
  target_campaigns: [] as number[],
  description: templateDescription,
  is_active: true,
})

const getFrequencyLabel = (frequency: string) => {
  const labels = {
    'one-time': '🎯 Une seule fois',
    daily: 'Quotidienne',
    weekly: 'Hebdomadaire',
    monthly: 'Mensuelle'
  }
  return labels[frequency] || frequency
}

const submit = () => {
  form.post(route('seo-queries.store'), {
    onSuccess: () => {
      // Redirection gérée par le contrôleur
    }
  })
}
</script>