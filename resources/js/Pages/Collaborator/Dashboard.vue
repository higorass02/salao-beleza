<template>
  <Layout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Minha agenda</h1>
        <p class="mt-1 text-sm text-gray-500">Bem-vindo, {{ page.props.auth?.user?.name }}</p>
      </div>

      <!-- Stats -->
      <div class="grid gap-4 sm:grid-cols-2">
        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ todayCount }}</p>
            <p class="text-sm text-gray-500">Agendamentos hoje</p>
          </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ weekCount }}</p>
            <p class="text-sm text-gray-500">Agendamentos esta semana</p>
          </div>
        </div>
      </div>

      <!-- Upcoming appointments -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-900">Próximos agendamentos</h2>
          <Link href="/collaborator/calendar" class="text-sm font-medium text-rose-600 hover:text-rose-700">
            Ver agenda →
          </Link>
        </div>

        <div v-if="!upcoming.length" class="px-6 py-12 text-center">
          <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          <p class="mt-2 text-sm text-gray-500">Nenhum agendamento futuro</p>
        </div>

        <ul v-else class="divide-y divide-gray-100">
          <li
            v-for="appt in upcoming"
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
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ appt.starts_at }}</p>
              <span
                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                :class="statusClass(appt.status)"
              >
                {{ statusLabel(appt.status) }}
              </span>
            </div>
          </li>
        </ul>
      </div>

      <!-- Quick actions -->
      <div class="grid gap-4 sm:grid-cols-2">
        <Link
          href="/collaborator/appointments/create"
          class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
        >
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Novo agendamento</p>
            <p class="text-xs text-gray-500">Criar agendamento</p>
          </div>
        </Link>

        <Link
          href="/collaborator/clients/create"
          class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
        >
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Novo cliente</p>
            <p class="text-xs text-gray-500">Cadastrar cliente</p>
          </div>
        </Link>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/CollaboratorLayout.vue';

const page = usePage();

defineProps({
  upcoming:   { type: Array,  default: () => [] },
  todayCount: { type: Number, default: 0 },
  weekCount:  { type: Number, default: 0 },
});

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
