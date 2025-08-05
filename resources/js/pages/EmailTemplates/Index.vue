<template>
  <AppLayout title="Modèles d'email">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Modèles d'email ({{ templates.total }})
        </h2>
        <Link
          :href="route('email-templates.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nouveau modèle
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Templates</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.total || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Actifs</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.active || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Brouillons</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.inactive || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Templates d'email -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">
                Gestion des modèles d'email
              </h3>
              <div class="flex items-center space-x-4">
                <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                  <option value="">Tous les types</option>
                  <option value="prospection">Prospection</option>
                  <option value="follow_up">Relance</option>
                  <option value="newsletter">Newsletter</option>
                </select>
                <input
                  type="search"
                  placeholder="Rechercher un template..."
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div v-if="templates.data.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-8a8 8 0 00-16 0v8m16 0H14m0 0h10v-4a6 6 0 00-10.712-3.714M14 40v-8a8 8 0 0116 0v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun template</h3>
              <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier modèle d'email.</p>
              <div class="mt-6">
                <Link
                  :href="route('email-templates.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Créer un template
                </Link>
              </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="template in templates.data"
                :key="template.id"
                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"
              >
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 truncate">
                      {{ template.name }}
                    </h4>
                    <div class="flex items-center space-x-2">
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          template.is_active 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-gray-100 text-gray-800'
                        ]"
                      >
                        {{ template.is_active ? 'Actif' : 'Inactif' }}
                      </span>
                      <button
                        @click="toggleTemplate(template)"
                        class="text-gray-400 hover:text-gray-600"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                    Objet: {{ template.subject }}
                  </p>
                  
                  <div class="bg-gray-50 rounded p-3 mb-4">
                    <p class="text-xs text-gray-600 italic line-clamp-3">
                      {{ template.content.substring(0, 100) }}...
                    </p>
                  </div>
                  
                  <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                      {{ template.segment_type || 'Général' }}
                    </span>
                    <span class="text-xs">
                      {{ new Date(template.created_at).toLocaleDateString('fr-FR') }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex space-x-2">
                      <button
                        @click="previewTemplate(template)"
                        class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                      >
                        Aperçu
                      </button>
                      <Link
                        :href="route('email-templates.duplicate', template.id)"
                        method="post"
                        class="text-sm text-gray-600 hover:text-gray-800 font-medium"
                      >
                        Dupliquer
                      </Link>
                    </div>
                    <div class="flex space-x-2">
                      <Link
                        :href="route('email-templates.edit', template.id)"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                      >
                        Modifier
                      </Link>
                      <Link
                        :href="route('email-templates.show', template.id)"
                        class="text-sm text-green-600 hover:text-green-800 font-medium"
                      >
                        Voir
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="templates.links && templates.data.length > 0" class="mt-6">
              <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Link
                    v-if="templates.prev_page_url"
                    :href="templates.prev_page_url"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Précédent
                  </Link>
                  <Link
                    v-if="templates.next_page_url"
                    :href="templates.next_page_url"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Suivant
                  </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Affichage de
                      <span class="font-medium">{{ templates.from }}</span>
                      à
                      <span class="font-medium">{{ templates.to }}</span>
                      sur
                      <span class="font-medium">{{ templates.total }}</span>
                      résultats
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <template v-for="(link, index) in templates.links" :key="index">
                        <Link
                          v-if="link.url"
                          :href="link.url"
                          :class="[
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                            link.active
                              ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            index === 0 ? 'rounded-l-md' : '',
                            index === templates.links.length - 1 ? 'rounded-r-md' : ''
                          ]"
                          v-html="link.label"
                        />
                        <span
                          v-else
                          :class="[
                            'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500',
                            index === 0 ? 'rounded-l-md' : '',
                            index === templates.links.length - 1 ? 'rounded-r-md' : ''
                          ]"
                          v-html="link.label"
                        />
                      </template>
                    </nav>
                  </div>
                </div>
              </nav>
            </div>
          </div>
        </div>

        <!-- Variables disponibles -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Variables disponibles</h4>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{business_name}</code>
                  <p class="text-gray-600 mt-1">Nom de l'entreprise</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{phone}</code>
                  <p class="text-gray-600 mt-1">Téléphone</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{email}</code>
                  <p class="text-gray-600 mt-1">Email du contact</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{website}</code>
                  <p class="text-gray-600 mt-1">Site web</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{address}</code>
                  <p class="text-gray-600 mt-1">Adresse</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{activity_type}</code>
                  <p class="text-gray-600 mt-1">Type d'activité</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{city}</code>
                  <p class="text-gray-600 mt-1">Ville</p>
                </div>
                <div>
                  <code class="bg-gray-200 px-2 py-1 rounded text-gray-800">{google_rating}</code>
                  <p class="text-gray-600 mt-1">Note Google</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal d'aperçu -->
    <div v-if="showPreview" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Aperçu du template</h3>
            <button @click="showPreview = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div v-if="previewData" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
              <div class="p-3 bg-gray-50 rounded-md">{{ previewData.subject }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contenu</label>
              <div class="p-3 bg-gray-50 rounded-md h-64 overflow-y-auto" v-html="previewData.content.replace(/\n/g, '<br>')"></div>
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <button
              @click="showPreview = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface EmailTemplate {
  id: number;
  name: string;
  subject: string;
  content: string;
  segment_type?: string;
  is_active: boolean;
  created_at: string;
}

interface Stats {
  total: number;
  active: number;
  inactive: number;
}

interface PaginatedTemplates {
  data: EmailTemplate[];
  links: any[];
  total: number;
  from: number;
  to: number;
  prev_page_url?: string;
  next_page_url?: string;
}

defineProps<{
  templates: PaginatedTemplates;
  stats?: Stats;
  filters?: any;
}>();

const showPreview = ref(false);
const previewData = ref<{subject: string, content: string} | null>(null);

const toggleTemplate = (template: EmailTemplate) => {
  router.post(route('email-templates.toggle', template.id));
};

const previewTemplate = async (template: EmailTemplate) => {
  try {
    const response = await fetch(route('email-templates.preview', template.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    const data = await response.json();
    previewData.value = data;
    showPreview.value = true;
  } catch (error) {
    console.error('Erreur lors de l\'aperçu:', error);
  }
};
</script>