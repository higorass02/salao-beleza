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
              type="text"
              :value="isoToBr(form.starts_at_date)"
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

        <!-- Recorrência -->
        <div>
          <label class="flex cursor-pointer items-center gap-3">
            <div class="relative">
              <input type="checkbox" v-model="form.is_recurring" class="sr-only peer" />
              <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
              <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
            </div>
            <span class="text-sm font-medium text-gray-700">Criar agendamentos recorrentes</span>
          </label>
        </div>

        <div v-if="form.is_recurring" class="space-y-3 rounded-lg border border-rose-100 bg-rose-50 p-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Frequência <span class="text-red-500">*</span></label>
              <select
                v-model="form.recurrence_type"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              >
                <option value="">Selecione</option>
                <option value="weekly">Semanal (a cada 7 dias)</option>
                <option value="biweekly">Quinzenal (a cada 15 dias)</option>
              </select>
              <p v-if="form.errors.recurrence_type" class="mt-1 text-xs text-red-600">{{ form.errors.recurrence_type }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Duração <span class="text-red-500">*</span></label>
              <select
                v-model="form.recurrence_months"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              >
                <option value="">Selecione</option>
                <option value="1">1 mês</option>
                <option value="2">2 meses</option>
                <option value="3">3 meses (máximo)</option>
              </select>
              <p v-if="form.errors.recurrence_months" class="mt-1 text-xs text-red-600">{{ form.errors.recurrence_months }}</p>
            </div>
          </div>

          <!-- Preview do último agendamento -->
          <div v-if="recurringPreview" class="flex items-center gap-2 rounded-md border border-rose-200 bg-white px-3 py-2 text-sm">
            <svg class="h-4 w-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
            <span class="text-gray-600">Previsão do último agendamento:</span>
            <span class="font-semibold text-rose-700">{{ recurringPreview }}</span>
          </div>
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

const isoToBr = (d) => { if (!d || d.length < 10) return ''; const [y, m, day] = d.substring(0, 10).split('-'); return `${day}/${m}/${y}`; };

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
  client_id:          null,
  employee_id:        '',
  service_id:         '',
  starts_at_date:     props.date,
  starts_at_time:     props.time,
  is_recurring:       false,
  recurrence_type:    '',
  recurrence_months:  '',
});

const recurringPreview = computed(() => {
  if (!form.is_recurring || !form.recurrence_type || !form.recurrence_months || !props.date) return null;
  const start    = new Date(props.date + 'T00:00:00');
  const until    = new Date(start);
  until.setMonth(until.getMonth() + parseInt(form.recurrence_months));
  const interval = form.recurrence_type === 'weekly' ? 7 : 14;
  let last = null;
  const cur = new Date(start);
  cur.setDate(cur.getDate() + interval);
  while (cur <= until) { last = new Date(cur); cur.setDate(cur.getDate() + interval); }
  if (!last) return 'Nenhum agendamento adicional no período';
  return last.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
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
    client_id:          selectedClient.value.id,
    employee_id:        data.employee_id,
    service_id:         data.service_id,
    starts_at:          `${data.starts_at_date} ${data.starts_at_time}:00`,
    is_recurring:       data.is_recurring,
    recurrence_type:    data.is_recurring ? data.recurrence_type  : null,
    recurrence_months:  data.is_recurring ? data.recurrence_months : null,
  })).post('/appointments', {
    onSuccess: () => emit('close'),
  });
}
</script>
