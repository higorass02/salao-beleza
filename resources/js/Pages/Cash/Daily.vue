<template>
  <Layout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Fechamento Diário</h1>
        <p class="mt-1 text-sm text-gray-500">Selecione um dia para gerar o relatório do caixa</p>
      </div>

      <!-- Date picker -->
      <div class="flex items-end gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700">Data</label>
          <input
            v-model="selectedDate"
            type="date"
            class="mt-1 block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
          />
        </div>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 transition-colors"
          @click="loadReport"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          Carregar relatório
        </button>
      </div>

      <!-- Report (only when a date is loaded) -->
      <template v-if="date">
        <!-- Status banner -->
        <div
          :class="isClosed
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-amber-200 bg-amber-50 text-amber-700'"
          class="flex items-center gap-3 rounded-lg border px-4 py-3 text-sm font-medium"
        >
          <svg v-if="isClosed" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <svg v-else class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <span v-if="isClosed">Caixa fechado em {{ formatDateTime(dailyClosing?.closed_at) }}</span>
          <span v-else>Caixa em aberto — {{ formattedDate }}</span>
        </div>

        <!-- Summary cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total geral</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ fmt(summary.total_value) }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Para prestadores</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ fmt(summary.provider_total) }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Para a casa</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ fmt(summary.store_total) }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Taxa de serviço</p>
            <p class="mt-1 text-2xl font-bold text-violet-600">{{ fmt(summary.house_fee_total) }}</p>
          </div>
        </div>

        <!-- Items table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">
              Atendimentos do dia
              <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ allItems.length }}</span>
            </h2>
            <button
              v-if="!isClosed"
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900 transition-colors"
              @click="showAddModal = true"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Adicionar entrada
            </button>
          </div>

          <div v-if="allItems.length === 0" class="px-6 py-12 text-center">
            <p class="text-sm text-gray-500">Nenhum atendimento registrado para este dia.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gray-50 text-left">
                  <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Horário</th>
                  <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Cliente</th>
                  <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Serviço</th>
                  <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Valor</th>
                  <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Para quem foi pago</th>
                  <th v-if="!isClosed" class="px-4 py-3"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in allItems" :key="`${item.type}-${item.id}`" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ item.starts_at }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900">{{ item.client_name }}</td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">{{ item.service_name }}</div>
                    <div v-if="item.include_house_fee" class="text-xs text-violet-500">+ taxa da casa</div>
                  </td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ fmt(item.service_value) }}</td>
                  <td class="px-4 py-3">
                    <select
                      v-if="!isClosed"
                      :value="item.paid_to"
                      class="block rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                      @change="updatePaidTo(item, $event.target.value)"
                    >
                      <option value="">Pendente</option>
                      <option value="store">Casa</option>
                      <option value="provider">Prestador</option>
                    </select>
                    <span v-else :class="paidToBadge(item.paid_to).class" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset">
                      {{ paidToBadge(item.paid_to).label }}
                    </span>
                  </td>
                  <td v-if="!isClosed" class="px-4 py-3 text-right">
                    <button
                      v-if="item.type === 'entry'"
                      type="button"
                      class="text-xs text-red-500 hover:text-red-700"
                      @click="removeEntry(item.id)"
                    >Remover</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paid-to breakdown (when closed) -->
        <div v-if="isClosed" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
          <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-base font-semibold text-gray-900">Resumo de recebimentos</h2>
          </div>
          <div class="grid divide-y divide-gray-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Pago à casa</p>
              <p class="mt-2 text-xl font-bold text-gray-900">{{ fmt(paidToBreakdown.store) }}</p>
              <p class="mt-1 text-xs text-gray-400">{{ paidToBreakdown.storeCount }} atendimento(s)</p>
            </div>
            <div class="p-6">
              <p class="text-sm font-medium text-gray-500">Pago ao prestador</p>
              <p class="mt-2 text-xl font-bold text-gray-900">{{ fmt(paidToBreakdown.provider) }}</p>
              <p class="mt-1 text-xs text-gray-400">{{ paidToBreakdown.providerCount }} atendimento(s)</p>
            </div>
          </div>
        </div>

        <!-- Close day button -->
        <div v-if="!isClosed" class="flex justify-end">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors"
            @click="confirmClose"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Fechar Caixa do Dia
          </button>
        </div>
      </template>
    </div>

    <!-- Add entry modal -->
    <div v-if="showAddModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <h3 class="text-base font-semibold text-gray-900">Adicionar entrada manual</h3>
          <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form class="space-y-4 p-6" @submit.prevent="submitEntry">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Horário <span class="text-red-500">*</span></label>
              <input v-model="entryForm.performed_at" type="time" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" required />
              <p v-if="entryForm.errors.performed_at" class="mt-1 text-xs text-red-600">{{ entryForm.errors.performed_at }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Valor (R$) <span class="text-red-500">*</span></label>
              <input v-model="entryForm.service_value" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" required />
              <p v-if="entryForm.errors.service_value" class="mt-1 text-xs text-red-600">{{ entryForm.errors.service_value }}</p>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
            <input v-model="entryForm.client_name" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" placeholder="Nome do cliente" required />
            <p v-if="entryForm.errors.client_name" class="mt-1 text-xs text-red-600">{{ entryForm.errors.client_name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Serviço <span class="text-red-500">*</span></label>
            <input v-model="entryForm.service_name" type="text" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500" placeholder="Nome do serviço" required />
            <p v-if="entryForm.errors.service_name" class="mt-1 text-xs text-red-600">{{ entryForm.errors.service_name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Para quem?</label>
            <select v-model="entryForm.paid_to" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500">
              <option value="">Pendente</option>
              <option value="store">Casa</option>
              <option value="provider">Prestador</option>
            </select>
          </div>
          <div>
            <label class="flex cursor-pointer items-center gap-3">
              <div class="relative">
                <input type="checkbox" v-model="entryForm.include_house_fee" class="sr-only peer" />
                <div class="h-5 w-9 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4" />
              </div>
              <span class="text-sm text-gray-700">Incluir taxa de serviço da casa ({{ settings.house_fee_rate ?? 0 }}%)</span>
            </label>
          </div>
          <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
            <button type="button" @click="showAddModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
            <button type="submit" :disabled="entryForm.processing" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50">
              <svg v-if="entryForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Adicionar
            </button>
          </div>
        </form>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  date:         { type: String, default: null },
  dailyClosing: { type: Object, default: null },
  appointments: { type: Array,  default: () => [] },
  entries:      { type: Array,  default: () => [] },
  preview:      { type: Object, default: null },
  settings:     { type: Object, default: () => ({}) },
});

const today = new Date().toISOString().split('T')[0];
const selectedDate = ref(props.date ?? today);
const showAddModal  = ref(false);

const isClosed = computed(() => props.dailyClosing?.status === 'closed');

const formattedDate = computed(() => {
  if (!props.date) return '';
  return new Date(props.date + 'T00:00:00').toLocaleDateString('pt-BR', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  });
});

const allItems = computed(() => {
  const appts = (props.appointments ?? []).map(a => ({ ...a, _sortKey: a.starts_at }));
  const ents  = (props.entries ?? []).map(e => ({ ...e, _sortKey: e.starts_at }));
  return [...appts, ...ents].sort((a, b) => a._sortKey.localeCompare(b._sortKey));
});

const houseRate = computed(() => parseFloat(props.settings?.house_fee_rate ?? 0));

const summary = computed(() => {
  if (isClosed.value && props.dailyClosing) {
    return {
      total_value:     parseFloat(props.dailyClosing.total_value ?? 0),
      provider_total:  parseFloat(props.dailyClosing.provider_total ?? 0),
      store_total:     parseFloat(props.dailyClosing.store_total ?? 0),
      house_fee_total: parseFloat(props.dailyClosing.house_fee_total ?? 0),
    };
  }
  if (props.preview) return {
    total_value:     parseFloat(props.preview.total_value ?? 0),
    provider_total:  parseFloat(props.preview.provider_total ?? 0),
    store_total:     parseFloat(props.preview.store_total ?? 0),
    house_fee_total: parseFloat(props.preview.house_fee_total ?? 0),
  };
  return { total_value: 0, provider_total: 0, store_total: 0, house_fee_total: 0 };
});

const paidToBreakdown = computed(() => {
  let storeVal = 0, providerVal = 0, storeCount = 0, providerCount = 0;
  for (const item of allItems.value) {
    const val = parseFloat(item.service_value ?? 0);
    if (item.paid_to === 'store')    { storeVal += val;    storeCount++;    }
    if (item.paid_to === 'provider') { providerVal += val; providerCount++; }
  }
  return { store: storeVal, provider: providerVal, storeCount, providerCount };
});

function fmt(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value ?? 0);
}

function formatDateTime(iso) {
  if (!iso) return '';
  return new Date(iso).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function paidToBadge(paid_to) {
  if (paid_to === 'store')    return { label: 'Casa',      class: 'bg-blue-50 text-blue-700 ring-blue-600/20' };
  if (paid_to === 'provider') return { label: 'Prestador', class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' };
  return { label: 'Pendente', class: 'bg-gray-100 text-gray-600 ring-gray-500/10' };
}

function loadReport() {
  if (!selectedDate.value) return;
  router.get(`/cash/daily/${selectedDate.value}`);
}

function updatePaidTo(item, paid_to) {
  const route = item.type === 'appointment'
    ? `/cash/daily/${props.date}/appointments/${item.id}`
    : `/cash/daily/${props.date}/entries/${item.id}`;
  router.patch(route, { paid_to: paid_to || null }, { preserveScroll: true });
}

function removeEntry(id) {
  if (!confirm('Remover esta entrada?')) return;
  router.delete(`/cash/daily/${props.date}/entries/${id}`);
}

function confirmClose() {
  if (!confirm(`Fechar o caixa de ${formattedDate.value}? Esta ação não pode ser desfeita.`)) return;
  router.post(`/cash/daily/${props.date}/close`);
}

// Entry form
const entryForm = useForm({
  performed_at:      '',
  client_name:       '',
  service_name:      '',
  service_value:     '',
  include_house_fee: false,
  paid_to:           '',
  notes:             '',
});

function submitEntry() {
  entryForm.post(`/cash/daily/${props.date}/entries`, {
    onSuccess: () => {
      showAddModal.value = false;
      entryForm.reset();
    },
  });
}
</script>
