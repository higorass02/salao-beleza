<template>
  <Layout>
    <div class="mx-auto max-w-2xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link
          href="/appointments"
          class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar agendamento</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ appointment.client?.name }}</p>
        </div>
      </div>

      <!-- Form -->
      <form class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submit">

        <!-- Data e Horário -->
        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Data <span class="text-red-500">*</span></label>
            <input
              v-model="form.starts_at_date"
              type="date"
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

        <!-- Funcionário -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Funcionário <span class="text-red-500">*</span></label>
          <select
            v-model="form.employee_id"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
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

        <!-- Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
          <ClientSearchInput v-model="selectedClient" />
          <p v-if="clientError" class="mt-1 text-xs text-red-600">{{ clientError }}</p>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
          <select
            v-model="form.status"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            :class="{ 'border-red-300': form.errors.status }"
            required
          >
            <option value="scheduled">Agendado</option>
            <option value="done">Concluído</option>
            <option value="canceled">Cancelado</option>
          </select>
          <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
        </div>

        <!-- Observações -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Observações</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            :class="{ 'border-red-300': form.errors.notes }"
          />
          <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
        </div>

        <!-- Erro de conflito -->
        <div v-if="form.errors.starts_at && form.errors.starts_at.includes('Conflito')" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
          {{ form.errors.starts_at }}
        </div>

        <!-- Ações -->
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
          <Link
            href="/appointments"
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
            {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
          </button>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';
import ClientSearchInput from '@/Components/ClientSearchInput.vue';

const props = defineProps({
  appointment: { type: Object, required: true },
  services:    { type: Array,  default: () => [] },
  employees:   { type: Array,  default: () => [] },
});

const selectedClient = ref(props.appointment.client ?? null);
const clientError    = ref('');

const form = useForm({
  client_id:      props.appointment.client_id,
  employee_id:    props.appointment.employee_id,
  service_id:     props.appointment.service_id,
  starts_at_date: props.appointment.starts_at?.substring(0, 10) ?? '',
  starts_at_time: props.appointment.starts_at?.substring(11, 16) ?? '',
  status:         props.appointment.status,
  notes:          props.appointment.notes ?? '',
});

watch(selectedClient, (client) => {
  form.client_id  = client?.id ?? null;
  clientError.value = '';
});

function submit() {
  if (!selectedClient.value) {
    clientError.value = 'Selecione um cliente';
    return;
  }
  form.transform((data) => ({
    client_id:   selectedClient.value.id,
    employee_id: data.employee_id,
    service_id:  data.service_id,
    starts_at:   `${data.starts_at_date} ${data.starts_at_time}:00`,
    status:      data.status,
    notes:       data.notes || null,
  })).put(`/appointments/${props.appointment.id}`, {
    preserveScroll: true,
  });
}
</script>
