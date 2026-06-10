<template>
  <Layout>
    <div class="mx-auto max-w-2xl space-y-6">
      <div class="flex items-center gap-3">
        <Link
          href="/collaborator"
          class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Novo agendamento</h1>
          <p class="mt-0.5 text-sm text-gray-500">Preencha os dados do agendamento</p>
        </div>
      </div>

      <form class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submit">

        <!-- Data e Horário -->
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Data <span class="text-red-500">*</span></label>
            <input
              v-maska="'##/##/####'"
              v-model="form.starts_at_date"
              type="text"
              inputmode="numeric"
              placeholder="dd/mm/aaaa"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.starts_at }"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Horário <span class="text-red-500">*</span></label>
            <input
              v-model="form.starts_at_time"
              type="time"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.starts_at }"
              required
            />
            <p v-if="form.errors.starts_at" class="mt-1 text-xs text-red-600">{{ form.errors.starts_at }}</p>
          </div>
        </div>

        <!-- Serviço -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Serviço <span class="text-red-500">*</span></label>
          <select
            v-model="form.service_id"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
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

        <!-- Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
          <ClientSearchInput v-model="selectedClient" />
          <p v-if="clientError" class="mt-1 text-xs text-red-600">{{ clientError }}</p>
          <p class="mt-1 text-xs text-gray-500">
            Cliente não cadastrado?
            <Link href="/clients/create" class="text-rose-600 hover:text-rose-700">Cadastrar agora</Link>
          </p>
        </div>

        <!-- Observações -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Observações</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            placeholder="Informações adicionais..."
          />
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

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
          <Link
            href="/collaborator"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </Link>
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
  </Layout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';
import Layout from '@/Layouts/CollaboratorLayout.vue';
import ClientSearchInput from '@/Components/ClientSearchInput.vue';

const brToIso = (d) => { if (!d || d.length !== 10) return ''; const [dd, mm, yy] = d.split('/'); return `${yy}-${mm}-${dd}`; };

defineProps({
  services:      { type: Array,  default: () => [] },
  myEmployeeId:  { type: Number, default: null },
});

const selectedClient = ref(null);
const clientError    = ref('');

const form = useForm({
  service_id:         '',
  starts_at_date:     '',
  starts_at_time:     '',
  notes:              '',
  is_recurring:       false,
  recurrence_type:    '',
  recurrence_months:  '',
});

const recurringPreview = computed(() => {
  if (!form.is_recurring || !form.recurrence_type || !form.recurrence_months || !form.starts_at_date) return null;
  const iso   = brToIso(form.starts_at_date);
  if (!iso) return null;
  const start = new Date(iso + 'T00:00:00');
  const until = new Date(start);
  until.setMonth(until.getMonth() + parseInt(form.recurrence_months));
  const interval = form.recurrence_type === 'weekly' ? 7 : 14;
  let last = null;
  const cur = new Date(start);
  cur.setDate(cur.getDate() + interval);
  while (cur <= until) { last = new Date(cur); cur.setDate(cur.getDate() + interval); }
  if (!last) return 'Nenhum agendamento adicional no período';
  return last.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
});

watch(selectedClient, () => { clientError.value = ''; });

function submit() {
  if (!selectedClient.value) {
    clientError.value = 'Selecione um cliente';
    return;
  }
  form.transform((data) => ({
    client_id:          selectedClient.value.id,
    service_id:         data.service_id,
    starts_at:          `${brToIso(data.starts_at_date)} ${data.starts_at_time}:00`,
    notes:              data.notes || null,
    is_recurring:       data.is_recurring,
    recurrence_type:    data.is_recurring ? data.recurrence_type   : null,
    recurrence_months:  data.is_recurring ? data.recurrence_months : null,
  })).post('/collaborator/appointments');
}
</script>
