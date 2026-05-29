<template>
  <Layout>
    <div class="space-y-6">
      <!-- Page header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Visão geral do salão</p>
      </div>

      <!-- Stats -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ stats.clients ?? 0 }}</p>
            <p class="text-sm text-gray-500">Clientes</p>
          </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ stats.employees ?? 0 }}</p>
            <p class="text-sm text-gray-500">Funcionários ativos</p>
          </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ stats.services ?? 0 }}</p>
            <p class="text-sm text-gray-500">Serviços ativos</p>
          </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900">{{ stats.upcoming ?? 0 }}</p>
            <p class="text-sm text-gray-500">Agendamentos futuros</p>
          </div>
        </div>
      </div>

      <!-- Upcoming appointments -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-900">Próximos agendamentos</h2>
          <Link href="/appointments" class="text-sm font-medium text-rose-600 hover:text-rose-700">
            Ver agenda →
          </Link>
        </div>

        <div v-if="!upcoming || upcoming.length === 0" class="px-6 py-12 text-center">
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
              <p class="truncate text-sm font-medium text-gray-900">{{ appt.client?.name }}</p>
              <p class="truncate text-xs text-gray-500">{{ appt.service?.name }} · {{ appt.employee?.name }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatDate(appt.starts_at) }}</p>
              <p class="text-xs text-gray-500">{{ formatTime(appt.starts_at) }}</p>
            </div>
          </li>
        </ul>
      </div>

      <!-- Quick actions -->
      <div class="grid gap-4 sm:grid-cols-3">
        <Link href="/clients/create" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
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

        <Link href="/employees/create" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Novo funcionário</p>
            <p class="text-xs text-gray-500">Adicionar funcionário</p>
          </div>
        </Link>

        <Link href="/services/create" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">Novo serviço</p>
            <p class="text-xs text-gray-500">Cadastrar serviço</p>
          </div>
        </Link>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';

defineProps({
  stats: { type: Object, default: () => ({}) },
  upcoming: { type: Array, default: () => [] },
});

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
}
function formatTime(iso) {
  return new Date(iso).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}
</script>
