<template>
  <AppLayout title="Détails du template">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ template.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('email-templates.edit', template.id)"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier
          </Link>
          <Link
            :href="route('email-templates.duplicate', template.id)"
            method="post"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Dupliquer
          </Link>
          <Link
            :href="route('email-templates.index')"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux templates
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Informations du template -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Informations du template
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
              Détails et contenu de ce template d'email.
            </p>
          </div>
          <div class="border-t border-gray-200">
            <dl>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Nom</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ template.name }}</dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Objet</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ template.subject }}</dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Type de segment</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  {{ template.segment_type || 'Général' }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    template.is_active 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ template.is_active ? 'Actif' : 'Inactif' }}
                  </span>
                </dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                  {{ new Date(template.created_at).toLocaleDateString('fr-FR') }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Contenu du template -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Contenu du template
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
              Aperçu du contenu avec les variables.
            </p>
          </div>
          <div class="border-t border-gray-200 p-6">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Objet de l'email</label>
                <div class="p-3 bg-white border border-gray-200 rounded-md">
                  {{ template.subject }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Corps de l'email</label>
                <div class="p-4 bg-white border border-gray-200 rounded-md max-h-96 overflow-y-auto">
                  <pre class="whitespace-pre-wrap text-sm text-gray-900">{{ template.content }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Aperçu avec données d'exemple -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Aperçu avec données d'exemple
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                  Voir comment le template s'affiche avec des données réelles.
                </p>
              </div>
              <button
                @click="generatePreview"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Générer aperçu
              </button>
            </div>
          </div>
          <div v-if="previewData" class="border-t border-gray-200 p-6">
            <div class="bg-blue-50 rounded-lg p-4">
              <div class="mb-4">
                <label class="block text-sm font-medium text-blue-900 mb-2">Objet (avec données)</label>
                <div class="p-3 bg-white border border-blue-200 rounded-md">
                  {{ previewData.subject }}
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-blue-900 mb-2">Corps (avec données)</label>
                <div class="p-4 bg-white border border-blue-200 rounded-md max-h-96 overflow-y-auto">
                  <pre class="whitespace-pre-wrap text-sm text-gray-900">{{ previewData.content }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Variables utilisées -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Variables utilisées dans ce template
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
              Liste des variables détectées dans le contenu.
            </p>
          </div>
          <div class="border-t border-gray-200 p-6">
            <div v-if="usedVariables.length === 0" class="text-center py-8 text-gray-500">
              Aucune variable détectée dans ce template.
            </div>
            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="variable in usedVariables" :key="variable" class="flex items-center p-2 bg-gray-50 rounded">
                <code class="bg-gray-200 px-2 py-1 rounded text-gray-800 text-sm">{{ variable }}</code>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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

const props = defineProps<{
  template: EmailTemplate;
}>();

const previewData = ref<{subject: string, content: string} | null>(null);

const usedVariables = computed(() => {
  const variables = [];
  const content = props.template.subject + ' ' + props.template.content;
  const matches = content.match(/\{[^}]+\}/g);
  if (matches) {
    return [...new Set(matches)];
  }
  return [];
});

const generatePreview = () => {
  const exampleData = {
    '{business_name}': 'Restaurant Le Gourmet',
    '{phone}': '01 23 45 67 89',
    '{email}': 'contact@legourmet.fr',
    '{website}': 'https://legourmet.fr',
    '{address}': '123 Rue de la Paix, 75001 Paris',
    '{activity_type}': 'restaurant',
    '{city}': 'Paris',
    '{google_rating}': '4.5'
  };
  
  let previewSubject = props.template.subject;
  let previewContent = props.template.content;
  
  Object.entries(exampleData).forEach(([variable, value]) => {
    previewSubject = previewSubject.replace(new RegExp(variable.replace(/[{}]/g, '\\$&'), 'g'), value);
    previewContent = previewContent.replace(new RegExp(variable.replace(/[{}]/g, '\\$&'), 'g'), value);
  });
  
  previewData.value = {
    subject: previewSubject,
    content: previewContent
  };
};
</script>