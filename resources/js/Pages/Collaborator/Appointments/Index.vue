<template>
  <Layout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Meus agendamentos</h1>
          <p class="mt-1 text-sm text-gray-500">{{ appointments.length }} agendamento(s)</p>
        </div>
        <Link
          href="/collaborator/appointments/create"
          class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Novo
        </Link>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div v-if="!appointments.length" class="px-6 py-12 text-center">
          <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          <p class="mt-2 text-sm text-gray-500">Nenhum agendamento encontrado</p>
        </div>

        <ul v-else class="divide-y divide-gray-100">
          <li
            v-for="appt in appointments"
            :key="appt.id"
            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50"
          >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium text-gray-900">{{ appt.client_name }}</p>
              <p class="truncate text-xs text-gray-500">{{ appt.service_name }}</p>
            </div>
            <div class="flex items-center gap-3">
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ appt.starts_at }}</p>
                <span
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="statusClass(appt.status)"
                >
                  {{ statusLabel(appt.status) }}
                </span>
              </div>
              <Link
                :href="`/collaborator/appointments/${appt.id}/edit`"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-gray-300 hover:text-gray-600 transition-colors"
                title="Editar"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                </svg>
              </Link>
              <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-red-200 hover:text-red-500 transition-colors"
                title="Excluir"
                @click="confirmDelete(appt)"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Delete confirmation -->
    <div
      v-if="deletingAppt"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4"
    >
      <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-base font-semibold text-gray-900">Excluir agendamento?</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ deletingAppt.client_name }} — {{ deletingAppt.starts_at }}
        </p>
        <div class="mt-5 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="deletingAppt = null"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors"
            @click="doDelete"
          >
            Excluir
          </button>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/CollaboratorLayout.vue';

defineProps({
  appointments: { type: Array, default: () => [] },
});

const deletingAppt = ref(null);

function confirmDelete(appt) {
  deletingAppt.value = appt;
}

function doDelete() {
  router.delete(`/collaborator/appointments/${deletingAppt.value.id}`, {
    onFinish: () => { deletingAppt.value = null; },
  });
}

function statusLabel(status) {
  return { scheduled: 'Agendado', done: 'Concluído', canceled: 'Cancelado' }[status] ?? status;
}

function statusClass(status) {
  return {
    scheduled: 'bg-blue-100 text-blue-700',
    done:      'bg-green-100 text-green-700',
    canceled:  'bg-red-100 text-red-700',
  }[status] ?? 'bg-gray-100 text-gray-700';
}
</script>
