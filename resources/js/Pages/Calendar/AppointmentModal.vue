<template>
  <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Novo agendamento</h2>
          <p class="text-xs text-gray-500">{{ formattedDate }}</p>
        </div>
        <button
          type="button"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
          @click="$emit('close')"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form class="space-y-4 p-6" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Data</label>
            <input
              type="date"
              :value="form.starts_at_date"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500 bg-gray-50"
              readonly
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Horário</label>
            <input
              type="time"
              v-model="form.starts_at_time"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            />
            <p v-if="form.errors.starts_at_time" class="mt-1 text-xs text-red-600">{{ form.errors.starts_at_time }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Serviço <span class="text-red-500">*</span></label>
          <select
            v-model="form.service_id"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            :class="{ 'border-red-300': form.errors.service_id }"
            required
          >
            <option value="">Selecione o serviço</option>
            <option v-for="s in services" :key="s.id" :value="s.id">
              {{ s.name }} — R$ {{ Number(s.price).toFixed(2) }} ({{ s.duration_minutes }} min)
            </option>
          </select>
          <p v-if="form.errors.service_id" class="mt-1 text-xs text-red-600">{{ form.errors.service_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Funcionário <span class="text-red-500">*</span></label>
          <select
            v-model="form.employee_id"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            :class="{ 'border-red-300': form.errors.employee_id }"
            required
          >
            <option value="">Selecione o funcionário</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">
              {{ e.name }} — {{ e.role }}
            </option>
          </select>
          <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
          <ClientSearchInput v-model="selectedClient" />
          <p v-if="clientError" class="mt-1 text-xs text-red-600">{{ clientError }}</p>
        </div>

        <div v-if="form.errors.starts_at" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
          {{ form.errors.starts_at }}
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
          <button
            type="button"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="$emit('close')"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50 transition-colors"
          >
            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ form.processing ? 'Agendando...' : 'Agendar' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ClientSearchInput from '@/Components/ClientSearchInput.vue';

const props = defineProps({
  date: String,
  time: String,
  services: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const selectedClient = ref(null);
const clientError = ref('');

const form = useForm({
  client_id: null,
  employee_id: '',
  service_id: '',
  starts_at_date: props.date,
  starts_at_time: props.time,
});

const formattedDate = computed(() => {
  if (!props.date) return '';
  return new Date(props.date + 'T00:00:00').toLocaleDateString('pt-BR', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
});

watch(selectedClient, (client) => {
  form.client_id = client?.id ?? null;
  clientError.value = '';
});

function submit() {
  if (!selectedClient.value) {
    clientError.value = 'Selecione um cliente';
    return;
  }
  form.transform((data) => ({
    client_id: selectedClient.value.id,
    employee_id: data.employee_id,
    service_id: data.service_id,
    starts_at: `${data.starts_at_date} ${data.starts_at_time}:00`,
  })).post('/appointments', {
    onSuccess: () => emit('close'),
  });
}
</script>
