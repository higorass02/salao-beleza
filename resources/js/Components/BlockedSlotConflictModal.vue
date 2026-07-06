<template>
  <BaseModal :show="show" @close="$emit('close')" max-width="lg">
    <div class="p-6">
      <div class="flex items-start gap-4 mb-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
          <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
        </div>
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Agendamentos no período</h2>
          <p class="text-sm text-gray-500 mt-0.5">
            O bloqueio foi criado, mas existem {{ conflicts.length }} agendamento{{ conflicts.length !== 1 ? 's' : '' }} neste período.
            Entre em contato com os clientes para reagendar.
          </p>
        </div>
      </div>

      <div class="space-y-2 mb-6 max-h-64 overflow-y-auto">
        <div
          v-for="appt in conflicts"
          :key="appt.id"
          class="rounded-xl border border-amber-200 bg-amber-50 p-3 flex items-center justify-between"
        >
          <div>
            <p class="text-sm font-medium text-gray-900">{{ appt.client?.name ?? '—' }}</p>
            <p class="text-xs text-gray-500">{{ formatDateTime(appt.starts_at) }} · {{ appt.service?.name ?? '—' }}</p>
          </div>
          <span class="text-xs font-medium text-amber-700 bg-amber-100 rounded-full px-2 py-0.5">
            Pendente reagendamento
          </span>
        </div>
      </div>

      <div class="flex justify-end">
        <button
          @click="$emit('close')"
          class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
        >
          Entendido
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import BaseModal from './BaseModal.vue'

defineEmits(['close'])

defineProps({
  show:      { type: Boolean, default: false },
  conflicts: { type: Array, default: () => [] },
})

function formatDateTime(dt) {
  if (!dt) return ''
  return new Date(dt).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}
</script>
