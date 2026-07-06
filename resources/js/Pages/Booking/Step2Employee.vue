<template>
  <BookingLayout :step="selectedEmployee ? 3 : 2">

    <!-- Chip do serviço escolhido -->
    <div class="mb-6 flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm">
      <svg class="h-4 w-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
      </svg>
      <span class="font-medium text-rose-700">{{ service.name }}</span>
      <span class="text-rose-400">·</span>
      <span class="text-rose-600">{{ service.duration_minutes }} min</span>
      <a href="/booking" class="ml-auto text-xs text-rose-400 hover:text-rose-600 underline underline-offset-2">
        Trocar serviço
      </a>
    </div>

    <!-- ── Fase 1: escolha do profissional ── -->
    <template v-if="!selectedEmployee">
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Escolha o profissional</h1>
      <p class="text-gray-500 mb-6 text-sm">Selecione com quem deseja ser atendido</p>

      <div class="grid gap-3 sm:grid-cols-2">
        <button
          v-for="emp in employees"
          :key="emp.id"
          @click="selectedEmployee = emp"
          class="group flex items-center gap-4 rounded-2xl border-2 border-gray-200 bg-white p-4 text-left shadow-sm transition-all hover:border-rose-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-rose-400"
        >
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-rose-600 text-white text-lg font-bold shadow-sm">
            {{ emp.name.charAt(0).toUpperCase() }}
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-gray-900 group-hover:text-rose-600 transition-colors">{{ emp.name }}</p>
            <p v-if="emp.role" class="text-sm text-gray-400 mt-0.5 truncate">{{ emp.role }}</p>
          </div>
          <svg class="ml-auto h-5 w-5 shrink-0 text-gray-300 group-hover:text-rose-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
        </button>
      </div>

      <p v-if="employees.length === 0" class="py-16 text-center text-gray-400">
        Nenhum profissional disponível para este serviço.
      </p>
    </template>

    <!-- ── Fase 2: calendário de horários ── -->
    <template v-else>
      <!-- Chip do profissional + botão voltar -->
      <div class="mb-6 flex items-center gap-3">
        <button
          @click="selectedEmployee = null"
          class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition-colors hover:border-gray-300 hover:text-gray-700"
          title="Voltar"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
          </svg>
        </button>

        <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm">
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-rose-600 text-white text-sm font-bold">
            {{ selectedEmployee.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900 leading-tight">{{ selectedEmployee.name }}</p>
            <p v-if="selectedEmployee.role" class="text-xs text-gray-400 leading-tight">{{ selectedEmployee.role }}</p>
          </div>
        </div>
      </div>

      <BookingCalendar
        :employee-id="selectedEmployee.id"
        :service-id="service.id"
        :service="service"
        :employee="selectedEmployee"
        @confirm="goToConfirm"
      />
    </template>

  </BookingLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import BookingLayout from './BookingLayout.vue'
import BookingCalendar from '@/Components/BookingCalendar.vue'

const props = defineProps({
  service:   Object,
  employees: Array,
})

const selectedEmployee = ref(null)

function goToConfirm({ startsAt }) {
  router.visit('/booking/confirm', {
    data: {
      service_id:  props.service.id,
      employee_id: selectedEmployee.value.id,
      starts_at:   startsAt,
    },
  })
}
</script>
