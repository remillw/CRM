<template>
  <AppLayout title="Modifier le template">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Modifier {{ template.name }}
        </h2>
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
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit">
              <div class="space-y-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Nom du template *
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ex: Template de prospection restaurant"
                  />
                  <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700">
                    Objet de l'email *
                  </label>
                  <input
                    id="subject"
                    v-model="form.subject"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Ex: Bonjour {business_name}, développons votre activité!"
                  />
                  <div v-if="form.errors.subject" class="mt-2 text-sm text-red-600">{{ form.errors.subject }}</div>
                </div>

                <div>
                  <label for="content" class="block text-sm font-medium text-gray-700">
                    Contenu de l'email *
                  </label>
                  <textarea
                    id="content"
                    v-model="form.content"
                    rows="12"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  ></textarea>
                  <div v-if="form.errors.content" class="mt-2 text-sm text-red-600">{{ form.errors.content }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="segment_type" class="block text-sm font-medium text-gray-700">
                      Type de segment
                    </label>
                    <select
                      id="segment_type"
                      v-model="form.segment_type"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                      <option value="">Général</option>
                      <option value="prospection">Prospection</option>
                      <option value="follow_up">Relance</option>
                      <option value="newsletter">Newsletter</option>
                      <option value="promotion">Promotion</option>
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">
                      Statut
                    </label>
                    <div class="mt-2">
                      <label class="inline-flex items-center">
                        <input
                          v-model="form.is_active"
                          type="checkbox"
                          class="form-checkbox text-blue-600"
                        />
                        <span class="ml-2">Template actif</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-6 space-x-3">
                <Link
                  :href="route('email-templates.index')"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                >
                  Annuler
                </Link>
                <button
                  type="button"
                  @click="sendTestEmail"
                  :disabled="!form.subject || !form.content || testEmailLoading"
                  class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 disabled:opacity-25"
                >
                  <div v-if="testEmailLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  📧 Test Email
                </button>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-25"
                >
                  Mettre à jour
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

interface EmailTemplate {
  id: number;
  name: string;
  subject: string;
  content: string;
  segment_type?: string;
  is_active: boolean;
}

const props = defineProps<{
  template: EmailTemplate;
}>();

const form = useForm({
  name: props.template.name,
  subject: props.template.subject,
  content: props.template.content,
  segment_type: props.template.segment_type || '',
  is_active: props.template.is_active
});

const testEmailLoading = ref(false);

const sendTestEmail = async () => {
  testEmailLoading.value = true;
  try {
    const response = await axios.post(route('email-templates.send-test'), {
      subject: form.subject,
      content: form.content
    });
    // Le message de succès sera affiché via la session flash du backend
  } catch (error) {
    console.error('Test email error:', error);
    alert('Erreur lors de l\'envoi de l\'email de test');
  } finally {
    testEmailLoading.value = false;
    // Recharger la page pour afficher le message de succès
    window.location.reload();
  }
};

const submit = () => {
  form.put(route('email-templates.update', props.template.id));
};
</script>