<template>
  <AppLayout :title="`${contact.business_name} - Détails`">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ contact.business_name }}
        </h2>
        <div class="flex items-center space-x-3">
          <Link 
            :href="route('contacts.edit', contact.id)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Modifier
          </Link>
          <Link 
            :href="route('contacts.index')" 
            class="text-gray-600 hover:text-gray-900"
          >
            ← Retour à la liste
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Informations principales -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informations de contact</h3>
          </div>
          <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt class="text-sm font-medium text-gray-500">Nom de l'entreprise</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ contact.business_name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Campagne</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <span v-if="contact.campaign">
                    {{ contact.campaign.name }} ({{ contact.campaign.activity_type }} - {{ contact.campaign.city }})
                  </span>
                  <span v-else class="text-gray-400">Aucune campagne</span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <a v-if="contact.phone" :href="`tel:${contact.phone}`" class="text-blue-600 hover:text-blue-800">
                    {{ contact.phone }}
                  </a>
                  <span v-else class="text-gray-400">Non renseigné</span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <a v-if="contact.email" :href="`mailto:${contact.email}`" class="text-blue-600 hover:text-blue-800">
                    {{ contact.email }}
                  </a>
                  <span v-else class="text-gray-400">Non renseigné</span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Site web</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <a v-if="contact.website" :href="contact.website" target="_blank" class="text-blue-600 hover:text-blue-800">
                    {{ contact.website }}
                  </a>
                  <span v-else class="text-gray-400">Non renseigné</span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ contact.address || 'Non renseignée' }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Note Google</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <span v-if="contact.google_rating">
                    ⭐ {{ contact.google_rating }} ({{ contact.review_count }} avis)
                  </span>
                  <span v-else class="text-gray-400">Non disponible</span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Statuts</dt>
                <dd class="mt-1 flex space-x-2">
                  <span :class="contact.is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ contact.is_verified ? 'Vérifié' : 'Non vérifié' }}
                  </span>
                  <span :class="contact.site_good ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    Site {{ contact.site_good ? 'OK' : 'KO' }}
                  </span>
                  <span :class="contact.can_command ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ contact.can_command ? 'Peut commander' : 'Ne peut pas commander' }}
                  </span>
                </dd>
              </div>
            </div>
          </div>
        </div>

        <!-- Requêtes SEO -->
        <div v-if="contact.website" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Analyse SEO</h3>
              <div class="flex space-x-2">
                <Link 
                  :href="route('seo-queries.create')"
                  class="px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 transition-colors"
                >
                  Créer une requête
                </Link>
                <Link 
                  :href="route('seo-queries.index')"
                  class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors"
                >
                  Voir toutes les requêtes
                </Link>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div v-if="seoQueries.length > 0" class="space-y-4">
              <div v-for="seoQuery in seoQueries" :key="seoQuery.id" class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <div>
                    <h4 class="font-medium text-gray-900">{{ seoQuery.name }}</h4>
                    <p class="text-sm text-gray-600">"{{ seoQuery.query }}"</p>
                    <p v-if="seoQuery.location" class="text-xs text-gray-500">📍 {{ seoQuery.location }}</p>
                  </div>
                  <div class="flex items-center space-x-2">
                    <Link 
                      :href="route('seo-queries.show', seoQuery.id)"
                      class="text-blue-600 hover:text-blue-800 text-sm"
                    >
                      Voir détails
                    </Link>
                    <button 
                      @click="analyzeSeoQuery(seoQuery.id)"
                      class="text-green-600 hover:text-green-800 text-sm"
                    >
                      Analyser
                    </button>
                  </div>
                </div>
                
                <div v-if="seoQuery.latest_result" class="bg-gray-50 rounded p-3">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                      <div v-if="seoQuery.latest_result.found">
                        <span 
                          :class="getPositionColor(seoQuery.latest_result.position)"
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        >
                          Position {{ seoQuery.latest_result.position }}
                        </span>
                      </div>
                      <div v-else>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                          Non trouvé
                        </span>
                      </div>
                      <div class="text-xs text-gray-500">
                        Analysé le {{ formatDate(seoQuery.latest_result.analyzed_at) }}
                      </div>
                    </div>
                    <div v-if="seoQuery.latest_result.url_found" class="text-xs">
                      <a :href="seoQuery.latest_result.url_found" target="_blank" class="text-blue-600 hover:text-blue-800">
                        Voir la page trouvée
                      </a>
                    </div>
                  </div>
                </div>
                <div v-else class="bg-yellow-50 rounded p-3 text-sm text-yellow-800">
                  Ce site n'a pas encore été analysé pour cette requête
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <div class="text-lg mb-2">Aucune requête SEO active</div>
              <div class="text-sm mb-4">Créez des requêtes SEO pour analyser le positionnement de ce site</div>
              <Link 
                :href="route('seo-queries.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              >
                Créer ma première requête SEO
              </Link>
            </div>
          </div>
        </div>

        <!-- Listes de contacts -->
        <div v-if="contact.lists && contact.lists.length > 0" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Listes de contacts</h3>
          </div>
          <div class="px-6 py-4">
            <div class="space-y-2">
              <div v-for="list in contact.lists" :key="list.id" class="flex items-center justify-between p-3 border rounded">
                <div>
                  <div class="font-medium">{{ list.name }}</div>
                  <div class="text-sm text-gray-600">{{ list.description }}</div>
                </div>
                <Link 
                  :href="route('contact-lists.show', list.id)"
                  class="text-blue-600 hover:text-blue-800 text-sm"
                >
                  Voir la liste
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Campagnes email -->
        <div v-if="contact.email_sends && contact.email_sends.length > 0" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Historique des campagnes email</h3>
          </div>
          <div class="px-6 py-4">
            <div class="space-y-2">
              <div v-for="emailSend in contact.email_sends" :key="emailSend.id" class="flex items-center justify-between p-3 border rounded">
                <div>
                  <div class="font-medium">{{ emailSend.campaign?.name || 'Campagne supprimée' }}</div>
                  <div class="text-sm text-gray-600">{{ emailSend.subject }}</div>
                  <div class="text-xs text-gray-500">Envoyé le {{ formatDate(emailSend.sent_at) }}</div>
                </div>
                <span 
                  :class="getEmailStatusColor(emailSend.status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ getEmailStatusLabel(emailSend.status) }}
                </span>
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
}

interface ContactList {
  id: number
  name: string
  description?: string
}

interface EmailSend {
  id: number
  subject: string
  status: string
  sent_at: string
  campaign?: {
    name: string
  }
}

interface SeoResult {
  position?: number
  found: boolean
  analyzed_at: string
  url_found?: string
}

interface SeoQuery {
  id: number
  name: string
  query: string
  location?: string
  latest_result?: SeoResult
}

interface Contact {
  id: number
  business_name: string
  phone?: string
  email?: string
  website?: string
  address?: string
  google_rating?: number
  review_count?: number
  is_verified: boolean
  site_good: boolean
  can_command: boolean
  campaign?: Campaign
  lists?: ContactList[]
  email_sends?: EmailSend[]
}

interface Props {
  contact: Contact
  seoQueries: SeoQuery[]
}

const props = defineProps<Props>()

const getPositionColor = (position?: number) => {
  if (!position) return 'bg-gray-100 text-gray-800'
  if (position <= 3) return 'bg-green-100 text-green-800'
  if (position <= 10) return 'bg-yellow-100 text-yellow-800'
  if (position <= 20) return 'bg-orange-100 text-orange-800'
  return 'bg-red-100 text-red-800'
}

const getEmailStatusColor = (status: string) => {
  switch (status) {
    case 'sent': return 'bg-green-100 text-green-800'
    case 'delivered': return 'bg-blue-100 text-blue-800'
    case 'opened': return 'bg-purple-100 text-purple-800'
    case 'clicked': return 'bg-indigo-100 text-indigo-800'
    case 'failed': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getEmailStatusLabel = (status: string) => {
  const labels = {
    sent: 'Envoyé',
    delivered: 'Délivré',
    opened: 'Ouvert',
    clicked: 'Cliqué',
    failed: 'Échec'
  }
  return labels[status] || status
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

const analyzeSeoQuery = (queryId: number) => {
  if (!confirm('Lancer l\'analyse SEO pour ce contact ?')) return
  
  router.post(route('seo-queries.analyze-contact', { query: queryId, contact: props.contact.id }))
}
</script>