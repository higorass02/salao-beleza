<template>
  <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Detalhes do agendamento</h2>
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

      <!-- Body -->
      <div class="space-y-4 p-6">
        <!-- Status -->
        <div class="flex items-center gap-2">
          <span
            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
            :class="statusClass"
          >
            {{ statusLabel }}
          </span>
        </div>

        <!-- Horário -->
        <div class="flex items-start gap-3">
          <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Horário</p>
            <p class="text-sm font-medium text-gray-900">{{ formattedTime }}</p>
          </div>
        </div>

        <!-- Cliente -->
        <div class="flex items-start gap-3">
          <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Cliente</p>
            <p class="text-sm font-medium text-gray-900">{{ appointment.client?.name ?? '—' }}</p>
            <p v-if="appointment.client?.phone" class="text-xs text-gray-500">{{ appointment.client.phone }}</p>
          </div>
        </div>

        <!-- Serviço -->
        <div class="flex items-start gap-3">
          <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Serviço</p>
            <p class="text-sm font-medium text-gray-900">{{ appointment.service?.name ?? '—' }}</p>
            <p v-if="appointment.service" class="text-xs text-gray-500">
              {{ appointment.service.duration_minutes }} min &middot; R$ {{ Number(appointment.service.price).toFixed(2) }}
            </p>
          </div>
        </div>

        <!-- Funcionário -->
        <div class="flex items-start gap-3">
          <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Funcionário</p>
            <p class="text-sm font-medium text-gray-900">{{ appointment.employee?.name ?? '—' }}</p>
            <p v-if="appointment.employee?.role" class="text-xs text-gray-500">{{ appointment.employee.role }}</p>
          </div>
        </div>

        <!-- Observações -->
        <div v-if="appointment.notes" class="flex items-start gap-3">
          <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500">Observações</p>
            <p class="text-sm text-gray-700">{{ appointment.notes }}</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
        <button
          type="button"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          @click="$emit('close')"
        >
          Fechar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  appointment: { type: Object, required: true },
});

defineEmits(['close']);

const statusMap = {
  scheduled: { label: 'Agendado',  class: 'bg-blue-100 text-blue-700' },
  done:      { label: 'Concluído', class: 'bg-green-100 text-green-700' },
  canceled:  { label: 'Cancelado', class: 'bg-gray-100 text-gray-600' },
};

const statusLabel = computed(() => statusMap[props.appointment.status]?.label ?? props.appointment.status);
const statusClass = computed(() => statusMap[props.appointment.status]?.class ?? 'bg-gray-100 text-gray-600');

const formattedDate = computed(() => {
  if (!props.appointment.starts_at) return '';
  return new Date(props.appointment.starts_at).toLocaleDateString('pt-BR', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
});

const formattedTime = computed(() => {
  if (!props.appointment.starts_at) return '';
  const start = new Date(props.appointment.starts_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  if (!props.appointment.ends_at) return start;
  const end = new Date(props.appointment.ends_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  return `${start} – ${end}`;
});
</script>
