<template>
  <Layout>
    <div class="space-y-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
        <p class="mt-1 text-sm text-gray-500">Parâmetros gerais do sistema</p>
      </div>

      <!-- Business hours + financial settings -->
      <form class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="saveSettings">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Horário de funcionamento</h2>
          <p class="mt-0.5 text-sm text-gray-500">Define o intervalo exibido no calendário de agendamentos.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Abertura</label>
            <input
              v-model="settingsForm.business_hours_start"
              type="time"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': settingsForm.errors.business_hours_start }"
            />
            <p v-if="settingsForm.errors.business_hours_start" class="mt-1 text-xs text-red-600">{{ settingsForm.errors.business_hours_start }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Fechamento</label>
            <input
              v-model="settingsForm.business_hours_end"
              type="time"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': settingsForm.errors.business_hours_end }"
            />
            <p v-if="settingsForm.errors.business_hours_end" class="mt-1 text-xs text-red-600">{{ settingsForm.errors.business_hours_end }}</p>
          </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
          <h2 class="text-base font-semibold text-gray-900">Financeiro</h2>
          <p class="mt-0.5 text-sm text-gray-500">Parâmetros usados no fechamento de caixa.</p>
        </div>

        <div class="max-w-xs">
          <label class="block text-sm font-medium text-gray-700">
            Taxa de serviço da casa (%)
          </label>
          <div class="relative mt-1">
            <input
              v-model="settingsForm.house_fee_rate"
              type="number"
              min="0"
              max="100"
              step="0.01"
              class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-8 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': settingsForm.errors.house_fee_rate }"
            />
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">%</span>
          </div>
          <p class="mt-1 text-xs text-gray-500">Acréscimo aplicado aos serviços com "Cobrar taxa da casa" ativado.</p>
          <p v-if="settingsForm.errors.house_fee_rate" class="mt-1 text-xs text-red-600">{{ settingsForm.errors.house_fee_rate }}</p>
        </div>

        <div class="flex justify-end border-t border-gray-100 pt-5">
          <button
            type="submit"
            :disabled="settingsForm.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50 transition-colors"
          >
            <svg v-if="settingsForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Salvar configurações
          </button>
        </div>
      </form>

      <!-- Services table -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-900">Serviços cadastrados</h2>
          <p class="mt-0.5 text-sm text-gray-500">
            A flag "Cobrar taxa da casa" é configurada em cada serviço individualmente.
          </p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Serviço</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Preço</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Taxa da casa</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                <th class="px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="service in services" :key="service.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ service.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">R$ {{ Number(service.price).toFixed(2) }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="service.include_house_fee
                      ? 'bg-violet-50 text-violet-700 ring-violet-600/20'
                      : 'bg-gray-100 text-gray-500 ring-gray-500/10'"
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                  >
                    {{ service.include_house_fee ? `Sim (${settingsForm.house_fee_rate}%)` : 'Não' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="service.active ? 'text-emerald-600' : 'text-gray-400'" class="text-xs font-medium">
                    {{ service.active ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <Link :href="`/services/${service.id}/edit`" class="text-xs font-medium text-rose-600 hover:text-rose-700">
                    Editar →
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  settings: { type: Object, default: () => ({}) },
  services: { type: Array,  default: () => [] },
});

const settingsForm = useForm({
  business_hours_start: props.settings.business_hours_start ?? '08:00',
  business_hours_end:   props.settings.business_hours_end   ?? '20:00',
  house_fee_rate:       props.settings.house_fee_rate        ?? '15',
});

function saveSettings() {
  settingsForm.put('/settings');
}
</script>
