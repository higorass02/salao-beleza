<template>
  <BookingLayout>
    <div class="flex flex-col items-center py-12 text-center">
      <!-- Ícone de sucesso -->
      <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
        <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-2">Agendamento confirmado!</h1>
      <p class="text-gray-500 mb-8">Seu horário foi reservado com sucesso.</p>

      <!-- Detalhes do agendamento -->
      <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm text-left mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Detalhes</h2>
        <dl class="space-y-3">
          <div v-if="appointment.client_name" class="flex justify-between text-sm">
            <dt class="text-gray-500">Cliente</dt>
            <dd class="font-medium text-gray-900">{{ appointment.client_name }}</dd>
          </div>
          <div v-if="appointment.service" class="flex justify-between text-sm">
            <dt class="text-gray-500">Serviço</dt>
            <dd class="font-medium text-gray-900">{{ appointment.service }}</dd>
          </div>
          <div v-if="appointment.employee" class="flex justify-between text-sm">
            <dt class="text-gray-500">Profissional</dt>
            <dd class="font-medium text-gray-900">{{ appointment.employee }}</dd>
          </div>
          <div v-if="appointment.starts_at" class="flex justify-between text-sm">
            <dt class="text-gray-500">Data e horário</dt>
            <dd class="font-medium text-gray-900">{{ formattedDate }}</dd>
          </div>
        </dl>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 w-full max-w-xs">
        <a
          href="/booking"
          class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          Novo agendamento
        </a>
        <a
          href="/"
          class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-400 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>
          Ir para o site
        </a>
      </div>
    </div>
  </BookingLayout>
</template>

<script setup>
import { computed } from 'vue'
import BookingLayout from './BookingLayout.vue'

const props = defineProps({
  appointment: Object,
})

const formattedDate = computed(() => {
  if (!props.appointment?.starts_at) return ''
  return new Date(props.appointment.starts_at).toLocaleString('pt-BR', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
})
</script>
