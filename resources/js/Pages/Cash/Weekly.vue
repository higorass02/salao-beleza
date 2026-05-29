<template>
  <Layout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Fechamento Semanal</h1>
        <p class="mt-1 text-sm text-gray-500">Balanço consolidado por semana</p>
      </div>

      <!-- Week navigation -->
      <div class="flex items-center gap-3">
        <button
          type="button"
          class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors"
          @click="changeWeek(-7)"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
          </svg>
        </button>
        <div class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm">
          {{ formatWeekLabel(weekStart) }} — {{ formatDate(weekEnd) }}
        </div>
        <button
          type="button"
          class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors"
          @click="changeWeek(7)"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
          </svg>
        </button>
        <button
          type="button"
          class="ml-2 text-sm text-rose-600 hover:text-rose-700 font-medium"
          @click="changeWeek(0, true)"
        >Semana atual</button>
      </div>

      <!-- Weekly summary cards (if closed) -->
      <div v-if="weeklyClosing" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">Total da semana</p>
          <p class="mt-1 text-2xl font-bold text-emerald-700">{{ fmt(weeklyClosing.total_value) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Para prestadores</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ fmt(weeklyClosing.provider_total) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Para a casa</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ fmt(weeklyClosing.store_total) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Taxas de serviço</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ fmt(weeklyClosing.house_fee_total) }}</p>
        </div>
      </div>

      <!-- Days table -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-900">Dias da semana</h2>
          <span
            v-if="weeklyClosing"
            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
          >
            Semana fechada em {{ formatDateTime(weeklyClosing.closed_at) }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="bg-gray-50 text-left">
                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Dia</th>
                <th class="px-6 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Prestadores</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Casa</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Taxas</th>
                <th class="px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="day in days" :key="day.date" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ day.label }}</td>
                <td class="px-6 py-4">
                  <span :class="statusBadge(day.status).class" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset">
                    {{ statusBadge(day.status).label }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ fmt(day.total_value) }}</td>
                <td class="px-6 py-4 text-right text-sm text-emerald-600">{{ fmt(day.provider_total) }}</td>
                <td class="px-6 py-4 text-right text-sm text-blue-600">{{ fmt(day.store_total) }}</td>
                <td class="px-6 py-4 text-right text-sm text-violet-600">{{ fmt(day.house_fee_total) }}</td>
                <td class="px-6 py-4 text-right">
                  <Link
                    :href="`/cash/daily/${day.date}`"
                    class="text-xs font-medium text-rose-600 hover:text-rose-700"
                  >Ver →</Link>
                </td>
              </tr>
            </tbody>
            <!-- Totals row -->
            <tfoot>
              <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
                <td class="px-6 py-4 text-sm text-gray-900">Total</td>
                <td class="px-6 py-4" />
                <td class="px-6 py-4 text-right text-sm text-gray-900">{{ fmt(weekTotals.total) }}</td>
                <td class="px-6 py-4 text-right text-sm text-emerald-600">{{ fmt(weekTotals.provider) }}</td>
                <td class="px-6 py-4 text-right text-sm text-blue-600">{{ fmt(weekTotals.store) }}</td>
                <td class="px-6 py-4 text-right text-sm text-violet-600">{{ fmt(weekTotals.houseFee) }}</td>
                <td class="px-6 py-4" />
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Close week button -->
      <div v-if="!weeklyClosing" class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
        <div>
          <p class="text-sm font-medium text-amber-800">Semana ainda não foi fechada</p>
          <p class="text-xs text-amber-600 mt-0.5">{{ closedDaysCount }}/7 dias com caixa fechado</p>
        </div>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors"
          @click="confirmClose"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
          </svg>
          Fechar Semana
        </button>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  weekStart:     { type: String, required: true },
  weekEnd:       { type: String, required: true },
  days:          { type: Array,  default: () => [] },
  weeklyClosing: { type: Object, default: null },
});

const weekTotals = computed(() => ({
  total:    props.days.reduce((s, d) => s + d.total_value,     0),
  provider: props.days.reduce((s, d) => s + d.provider_total,  0),
  store:    props.days.reduce((s, d) => s + d.store_total,      0),
  houseFee: props.days.reduce((s, d) => s + d.house_fee_total,  0),
}));

const closedDaysCount = computed(() => props.days.filter(d => d.status === 'closed').length);

function fmt(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value ?? 0);
}

function formatDate(iso) {
  return new Date(iso + 'T00:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatWeekLabel(iso) {
  return new Date(iso + 'T00:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatDateTime(iso) {
  if (!iso) return '';
  return new Date(iso).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}

function statusBadge(status) {
  if (status === 'closed')  return { label: 'Fechado', class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' };
  if (status === 'open')    return { label: 'Aberto',  class: 'bg-amber-50 text-amber-700 ring-amber-600/20' };
  return { label: 'Pendente', class: 'bg-gray-100 text-gray-600 ring-gray-500/10' };
}

function changeWeek(days, goToday = false) {
  const base = goToday
    ? new Date()
    : new Date(props.weekStart + 'T00:00:00');

  if (!goToday) base.setDate(base.getDate() + days);

  const monday = new Date(base);
  monday.setDate(monday.getDate() - ((monday.getDay() + 6) % 7));
  const iso = monday.toISOString().split('T')[0];
  router.get('/weekly', { week: iso });
}

function confirmClose() {
  if (!confirm('Fechar o balanço desta semana? Os valores serão salvos.')) return;
  router.post('/weekly/close', { week_start: props.weekStart });
}
</script>
