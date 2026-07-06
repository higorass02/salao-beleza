<template>
  <BookingLayout :step="1">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Escolha o serviço</h1>
    <p class="text-gray-500 mb-6">Selecione o serviço que deseja agendar</p>

    <div class="grid gap-4 sm:grid-cols-2">
      <button
        v-for="service in services"
        :key="service.id"
        @click="select(service)"
        class="group text-left rounded-2xl border-2 border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-rose-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-rose-400"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500 group-hover:bg-rose-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-gray-900 group-hover:text-rose-600">{{ service.name }}</p>
            <p v-if="service.description" class="mt-0.5 text-sm text-gray-500 line-clamp-2">{{ service.description }}</p>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-sm">
          <span class="inline-flex items-center gap-1 text-gray-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ service.duration_minutes }} min
          </span>
          <span v-if="service.price" class="font-semibold text-rose-600">
            R$ {{ Number(service.price).toFixed(2).replace('.', ',') }}
          </span>
        </div>
      </button>
    </div>

    <p v-if="services.length === 0" class="text-center text-gray-400 py-16">
      Nenhum serviço disponível no momento.
    </p>
  </BookingLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import BookingLayout from './BookingLayout.vue'

const props = defineProps({
  services: Array,
})

function select(service) {
  router.visit(`/booking/service/${service.id}/employees`)
}
</script>
