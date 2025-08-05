<template>
  <AppLayout title="Listes de contacts">
    <template #header>
      <div class="flex justify-between items-center w-full">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Listes de contacts ({{ lists.total }})
        </h2>
        <Link
          :href="route('contact-lists.create')"
          class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 shadow-lg transform hover:scale-105 transition-all duration-200"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          ➕ CRÉER UNE LISTE
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Listes</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.total_lists || 0 }}</dd>
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
                    <dt class="text-sm font-medium text-gray-500 truncate">Listes Actives</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.active_lists || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Contacts en Liste</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats?.total_contacts_in_lists || 0 }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Listes de contacts -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">
                Gestion des listes de contacts
              </h3>
              <div class="flex items-center space-x-4">
                <input
                  type="search"
                  placeholder="Rechercher une liste..."
                  class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div v-if="lists.data.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune liste</h3>
              <p class="mt-1 text-sm text-gray-500">Utilisez le bouton "➕ CRÉER UNE LISTE" en haut pour créer votre première liste de contacts.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="list in lists.data"
                :key="list.id"
                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"
              >
                <div class="p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 truncate">
                      {{ list.name }}
                    </h4>
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        list.status === 'active' 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ list.status === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  
                  <p v-if="list.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
                    {{ list.description }}
                  </p>
                  
                  <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                      </svg>
                      {{ list.contacts_count }} contacts
                    </span>
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                      </svg>
                      {{ list.segments_count }} segments
                    </span>
                  </div>

                  <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <span class="text-xs text-gray-500">
                      Créée le {{ new Date(list.created_at).toLocaleDateString('fr-FR') }}
                    </span>
                    <div class="flex space-x-2">
                      <Link
                        :href="route('contact-lists.show', list.id)"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                      >
                        Voir
                      </Link>
                      <Link
                        :href="route('contact-lists.edit', list.id)"
                        class="text-sm text-gray-600 hover:text-gray-800 font-medium"
                      >
                        Modifier
                      </Link>
                      <Link
                        :href="route('contact-lists.export', list.id)"
                        class="text-sm text-green-600 hover:text-green-800 font-medium"
                      >
                        Exporter
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="lists.links && lists.data.length > 0" class="mt-6">
              <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Link
                    v-if="lists.prev_page_url"
                    :href="lists.prev_page_url"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Précédent
                  </Link>
                  <Link
                    v-if="lists.next_page_url"
                    :href="lists.next_page_url"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Suivant
                  </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Affichage de
                      <span class="font-medium">{{ lists.from }}</span>
                      à
                      <span class="font-medium">{{ lists.to }}</span>
                      sur
                      <span class="font-medium">{{ lists.total }}</span>
                      résultats
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <template v-for="(link, index) in lists.links" :key="index">
                        <Link
                          v-if="link.url"
                          :href="link.url"
                          :class="[
                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                            link.active
                              ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                            index === 0 ? 'rounded-l-md' : '',
                            index === lists.links.length - 1 ? 'rounded-r-md' : ''
                          ]"
                          v-html="link.label"
                        />
                        <span
                          v-else
                          :class="[
                            'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500',
                            index === 0 ? 'rounded-l-md' : '',
                            index === lists.links.length - 1 ? 'rounded-r-md' : ''
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
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface ContactList {
  id: number;
  name: string;
  description?: string;
  status: 'active' | 'inactive';
  contacts_count: number;
  segments_count: number;
  created_at: string;
}

interface Stats {
  total_lists: number;
  active_lists: number;
  total_contacts_in_lists: number;
}

interface PaginatedLists {
  data: ContactList[];
  links: any[];
  total: number;
  from: number;
  to: number;
  prev_page_url?: string;
  next_page_url?: string;
}

defineProps<{
  lists: PaginatedLists;
  stats?: Stats;
  filters?: any;
}>();
</script>