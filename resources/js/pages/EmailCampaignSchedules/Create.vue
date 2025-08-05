<template>
  <AppLayout title="Programmer une campagne email">
    <template #header>
      <div class="flex justify-between items-center w-full">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Programmer une nouvelle campagne email
        </h2>
        <Link
          :href="route('email-campaign-schedules.index')"
          class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Retour
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit">
              <div class="space-y-6">
                
                <!-- Informations de base -->
                <div class="border-b border-gray-200 pb-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Informations de base</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="name" class="block text-sm font-medium text-gray-700">
                        Nom de la campagne *
                      </label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Ex: Prospection Pizzerias Lyon"
                      />
                      <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <div>
                      <label for="template_id" class="block text-sm font-medium text-gray-700">
                        Template email *
                      </label>
                      <select
                        id="template_id"
                        v-model="form.template_id"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      >
                        <option value="">Choisir un template</option>
                        <option v-for="template in templates" :key="template.id" :value="template.id">
                          {{ template.name }}
                        </option>
                      </select>
                      <div v-if="form.errors.template_id" class="mt-2 text-sm text-red-600">{{ form.errors.template_id }}</div>
                    </div>
                  </div>

                  <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                      Description
                    </label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Description de cette campagne..."
                    ></textarea>
                  </div>
                </div>

                <!-- Sélection des listes -->
                <div class="border-b border-gray-200 pb-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Listes destinataires</h3>
                  
                  <div class="space-y-3">
                    <label v-for="list in contactLists" :key="list.id" class="flex items-center">
                      <input
                        v-model="form.contact_list_ids"
                        type="checkbox"
                        :value="list.id"
                        class="form-checkbox text-blue-600 h-4 w-4"
                      />
                      <span class="ml-3 text-sm">
                        <span class="font-medium">{{ list.name }}</span>
                        <span class="text-gray-500">({{ list.contacts_count }} contacts)</span>
                        <span v-if="list.name.toLowerCase().includes('test')" class="ml-2 px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">
                          🧪 LISTE DE TEST
                        </span>
                      </span>
                    </label>
                  </div>
                  <div v-if="form.errors.contact_list_ids" class="mt-2 text-sm text-red-600">{{ form.errors.contact_list_ids }}</div>
                </div>

                <!-- Programmation -->
                <div class="border-b border-gray-200 pb-6">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Programmation</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quand envoyer ?
                      </label>
                      <div class="space-y-2">
                        <label class="flex items-center">
                          <input
                            v-model="form.send_type"
                            type="radio"
                            value="now"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Maintenant</span>
                        </label>
                        <label class="flex items-center">
                          <input
                            v-model="form.send_type"
                            type="radio"
                            value="scheduled"
                            class="form-radio text-blue-600"
                          />
                          <span class="ml-2 text-sm">Programmer</span>
                        </label>
                      </div>
                    </div>

                    <div v-if="form.send_type === 'scheduled'">
                      <label for="scheduled_date" class="block text-sm font-medium text-gray-700">
                        Date d'envoi
                      </label>
                      <input
                        id="scheduled_date"
                        v-model="form.scheduled_date"
                        type="date"
                        :min="new Date().toISOString().split('T')[0]"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>

                    <div v-if="form.send_type === 'scheduled'">
                      <label for="scheduled_time" class="block text-sm font-medium text-gray-700">
                        Heure d'envoi
                      </label>
                      <input
                        id="scheduled_time"
                        v-model="form.scheduled_time"
                        type="time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Statistiques prévisionnelles -->
                <div class="space-y-4">
                  <!-- Stats campagne principale -->
                  <div class="bg-blue-50 rounded-lg p-4" v-if="totalRecipients > 0">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">📊 Aperçu de la campagne principale</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                      <div>
                        <span class="text-blue-600 font-medium">{{ totalRecipients }}</span>
                        <span class="text-blue-800 ml-1">contacts total</span>
                      </div>
                      <div>
                        <span class="text-green-600 font-medium">{{ Math.round(totalRecipients * 0.25) }}</span>
                        <span class="text-blue-800 ml-1">ouvertures estimées</span>
                      </div>
                      <div>
                        <span class="text-purple-600 font-medium">{{ Math.round(totalRecipients * 0.05) }}</span>
                        <span class="text-blue-800 ml-1">clics estimés</span>
                      </div>
                    </div>
                  </div>

                  <!-- Stats test si disponible -->
                  <div class="bg-orange-50 rounded-lg p-4" v-if="hasTestList">
                    <h4 class="text-sm font-medium text-orange-900 mb-2">🧪 Test disponible</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <span class="text-orange-600 font-medium">{{ testListContacts }}</span>
                        <span class="text-orange-800 ml-1">contacts de test</span>
                      </div>
                      <div class="text-orange-700">
                        Testez votre email avant l'envoi à grande échelle
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-end space-x-4 pt-6">
                  <Link
                    :href="route('email-campaign-schedules.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"
                  >
                    Annuler
                  </Link>
                  
                  <!-- Bouton Test (toujours disponible si template sélectionné) -->
                  <button
                    v-if="form.template_id && hasTestList"
                    type="button"
                    @click="sendTest"
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700"
                  >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    🧪 TESTER AVANT ENVOI
                  </button>
                  
                  <!-- Message si pas de liste de test -->
                  <div v-else-if="form.template_id && !hasTestList" class="text-sm text-orange-600 bg-orange-50 px-3 py-2 rounded-md">
                    ⚠️ Créez une liste de test pour pouvoir tester vos emails avant envoi
                  </div>

                  <!-- Bouton Envoyer / Programmer -->
                  <button
                    type="submit"
                    :disabled="form.processing || totalRecipients === 0"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50"
                  >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.send_type === 'now' ? '📧 ENVOYER MAINTENANT' : '⏰ PROGRAMMER' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface ContactList {
  id: number;
  name: string;
  contacts_count: number;
}

interface EmailTemplate {
  id: number;
  name: string;
  subject: string;
}

const props = defineProps<{
  contactLists: ContactList[];
  templates: EmailTemplate[];
}>();

const form = useForm({
  name: '',
  description: '',
  template_id: '',
  contact_list_ids: [] as number[],
  send_type: 'now',
  scheduled_date: '',
  scheduled_time: '09:00'
});

// Calculs de statistiques
const totalRecipients = computed(() => {
  return props.contactLists
    .filter(list => form.contact_list_ids.includes(list.id))
    .reduce((total, list) => total + list.contacts_count, 0);
});

const testListSelected = computed(() => {
  return props.contactLists
    .filter(list => form.contact_list_ids.includes(list.id))
    .some(list => list.name.toLowerCase().includes('test'));
});

const hasTestList = computed(() => {
  return props.contactLists.some(list => list.name.toLowerCase().includes('test'));
});

const testListContacts = computed(() => {
  return props.contactLists
    .filter(list => list.name.toLowerCase().includes('test'))
    .reduce((total, list) => total + list.contacts_count, 0);
});

const submit = () => {
  form.post(route('email-campaign-schedules.store'));
};

const sendTest = () => {
  // Trouve TOUTES les listes de test (même si pas sélectionnées)
  const testListIds = props.contactLists
    .filter(list => list.name.toLowerCase().includes('test'))
    .map(list => list.id);
  
  if (testListIds.length === 0) {
    alert('Aucune liste de test trouvée. Créez une liste avec "test" dans le nom.');
    return;
  }
  
  if (confirm(`Envoyer un test à ${testListContacts.value} contacts de test ?`)) {
    // Utilise useForm pour créer un nouveau formulaire spécifique au test
    const testForm = useForm({
      name: (form.name || 'Test Email') + ' - TEST',
      description: form.description,
      template_id: form.template_id,
      contact_list_ids: testListIds,
      send_type: 'now',
      is_test: true
    });
    
    testForm.post(route('email-campaign-schedules.store'));
  }
};
</script>