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

      <!-- Barra de ações da semana -->
      <div
        class="flex items-center justify-between rounded-xl border px-5 py-4"
        :class="weeklyClosing
          ? 'border-emerald-200 bg-emerald-50'
          : 'border-amber-200 bg-amber-50'"
      >
        <div>
          <p class="text-sm font-medium" :class="weeklyClosing ? 'text-emerald-800' : 'text-amber-800'">
            {{ weeklyClosing ? 'Semana fechada' : 'Semana ainda não foi fechada' }}
          </p>
          <p class="text-xs mt-0.5" :class="weeklyClosing ? 'text-emerald-600' : 'text-amber-600'">
            {{ weeklyClosing ? `Fechada em ${formatDateTime(weeklyClosing.closed_at)}` : `${closedDaysCount}/7 dias com caixa fechado` }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Recalcular (só quando já fechada) -->
          <button
            v-if="weeklyClosing"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="recalculate"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Recalcular
          </button>
          <!-- Ver resumo (sempre visível) -->
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="showCloseModal = true"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
            Ver Resumo
          </button>
          <!-- Fechar semana (só se ainda não fechada) -->
          <button
            v-if="!weeklyClosing"
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
    </div>
    <!-- Modal de confirmação de fechamento semanal -->
    <div v-if="showCloseModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Fechar semana</h3>
            <p class="text-xs text-gray-500">{{ formatWeekLabel(weekStart) }} — {{ formatDate(weekEnd) }}</p>
          </div>
          <button type="button" class="text-gray-400 hover:text-gray-600" @click="showCloseModal = false">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="overflow-y-auto p-6 space-y-6">

          <!-- Resumo da casa -->
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Resumo da casa</p>
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div>
                <p class="text-gray-400 text-xs">Total de taxas</p>
                <p class="font-bold text-violet-600 text-base">{{ fmt(houseSummary.totalFees) }}</p>
              </div>
              <div>
                <p class="text-gray-400 text-xs">Recebido pela casa</p>
                <p class="font-bold text-blue-600 text-base">{{ fmt(houseSummary.receivedByStore) }}</p>
              </div>
              <div>
                <p class="text-gray-400 text-xs">Pendente</p>
                <p class="font-bold text-amber-600 text-base">{{ fmt(houseSummary.pending) }}</p>
              </div>
            </div>
          </div>

          <!-- Acerto por prestador -->
          <div v-if="providerSummary.filter(p => p.id !== 'manual').length > 0" class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Acerto por prestador</p>

            <div
              v-for="p in providerSummary.filter(p => p.id !== 'manual')"
              :key="p.id"
              class="overflow-hidden rounded-xl border border-gray-200 bg-white"
            >
              <!-- Nome -->
              <div class="flex items-center justify-between bg-gray-50 px-4 py-2.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-900">{{ p.name }}</span>
                <span class="text-sm font-medium text-gray-600">{{ fmt(p.total_services) }}</span>
              </div>

              <!-- Distribuição -->
              <div class="grid grid-cols-3 divide-x divide-gray-100 text-sm">
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Taxa da casa</p>
                  <p class="font-medium text-violet-600">{{ fmt(p.house_fee) }}</p>
                </div>
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Parte do prestador</p>
                  <p class="font-medium text-emerald-600">{{ fmt(p.provider_share) }}</p>
                </div>
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Pendente</p>
                  <p :class="p.pending > 0 ? 'text-amber-600 font-medium' : 'text-gray-400'">{{ fmt(p.pending) }}</p>
                </div>
              </div>

              <!-- Fluxo físico -->
              <div class="grid grid-cols-2 divide-x divide-gray-100 border-t border-gray-100 text-sm">
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Pago ao prestador</p>
                  <p class="font-medium text-gray-900">{{ fmt(p.received_by_provider) }}</p>
                </div>
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Pago à casa</p>
                  <p class="font-medium text-gray-900">{{ fmt(p.received_by_store) }}</p>
                </div>
              </div>

              <!-- Saldo -->
              <div
                class="border-t px-4 py-2.5 flex items-center justify-between text-sm font-semibold"
                :class="p.pending > 0
                  ? 'bg-amber-50 border-amber-200 text-amber-700'
                  : p.net > 0
                    ? 'bg-blue-50 border-blue-200 text-blue-700'
                    : p.net < 0
                      ? 'bg-rose-50 border-rose-200 text-rose-700'
                      : 'bg-emerald-50 border-emerald-200 text-emerald-700'"
              >
                <span>
                  {{ p.pending > 0 ? '⏳ Há pendências'
                    : p.net > 0  ? '🏠 Casa deve ao prestador'
                    : p.net < 0  ? '⚠️ Prestador deve à casa'
                    : '✓ Acertado' }}
                </span>
                <span v-if="p.net !== 0 && p.pending === 0" class="text-base font-bold">
                  {{ fmt(Math.abs(p.net)) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Total geral da semana -->
          <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-5 py-3 text-sm font-semibold text-gray-900">
            <span>Total geral da semana</span>
            <span class="text-lg font-bold">{{ fmt(weekTotals.total) }}</span>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 shrink-0">
          <button
            v-if="weeklyClosing"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="recalculate"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Recalcular
          </button>
          <span v-else />
          <div class="flex items-center gap-3">
          <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors" @click="showCloseModal = false">
            Cancelar
          </button>
          <button
            v-if="!weeklyClosing"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 transition-colors"
            @click="submitClose"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Confirmar fechamento
          </button>
          </div>
        </div>
      </div>
    </div>

  </Layout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  weekStart:       { type: String, required: true },
  weekEnd:         { type: String, required: true },
  days:            { type: Array,  default: () => [] },
  weeklyClosing:   { type: Object, default: null },
  providerSummary: { type: Array,  default: () => [] },
});

const weekTotals = computed(() => ({
  total:    props.days.reduce((s, d) => s + d.total_value,     0),
  provider: props.days.reduce((s, d) => s + d.provider_total,  0),
  store:    props.days.reduce((s, d) => s + d.store_total,      0),
  houseFee: props.days.reduce((s, d) => s + d.house_fee_total,  0),
}));

const closedDaysCount = computed(() => props.days.filter(d => d.status === 'closed').length);

const showCloseModal = ref(false);

const houseSummary = computed(() => {
  let totalFees = 0, receivedByStore = 0, pending = 0;
  for (const p of props.providerSummary) {
    totalFees      += p.house_fee;
    receivedByStore += p.received_by_store;
    pending        += p.pending;
  }
  return {
    totalFees:       parseFloat(totalFees.toFixed(2)),
    receivedByStore: parseFloat(receivedByStore.toFixed(2)),
    pending:         parseFloat(pending.toFixed(2)),
  };
});

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

function recalculate() {
  showCloseModal.value = false;
  router.post('/weekly/recalculate', { week_start: props.weekStart });
}

function confirmClose() {
  showCloseModal.value = true;
}

function submitClose() {
  showCloseModal.value = false;
  router.post('/weekly/close', { week_start: props.weekStart });
}
</script>
