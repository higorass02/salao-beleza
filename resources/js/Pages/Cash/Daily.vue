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
            v-maska="'##/##/####'"
            v-model="selectedDate"
            type="text"
            inputmode="numeric"
            placeholder="dd/mm/aaaa"
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
          <div class="ml-auto flex items-center gap-2">
            <button
              v-if="isClosed"
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-current/30 px-3 py-1.5 text-xs font-medium opacity-80 hover:opacity-100 transition-opacity"
              @click="reopenDay"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
              </svg>
              Revisar
            </button>
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-current/30 px-3 py-1.5 text-xs font-medium opacity-80 hover:opacity-100 transition-opacity"
              @click="showSummaryModal = true"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
              Ver Resumo
            </button>
          </div>
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
                  <th class="px-4 py-3 text-xs font-medium uppercase tracking-wide text-gray-500">Prestador</th>
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
                  <td class="px-4 py-3 text-sm text-gray-600">{{ item.employee_name ?? '—' }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ fmt(item.service_value) }}</td>
                  <td class="px-4 py-3">
                    <select
                      v-if="!isClosed && !providerClosings[item.employee_id]"
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

        <!-- Acerto por prestador -->
        <div v-if="byEmployee.length > 0" class="space-y-3">
          <div class="flex items-center gap-2">
            <h2 class="text-base font-semibold text-gray-900">Acerto por prestador</h2>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
              Atualiza em tempo real
            </span>
          </div>

          <div
            v-for="emp in byEmployee"
            :key="emp.id"
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
          >
            <!-- Header do prestador -->
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-5 py-3">
              <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                  {{ emp.name?.[0]?.toUpperCase() }}
                </div>
                <span class="text-sm font-semibold text-gray-900">{{ emp.name }}</span>
                <span class="text-xs text-gray-400">· {{ emp.count }} atendimento(s)</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700">{{ fmt(emp.totalServices) }}</span>
                <!-- Badge de fechado -->
                <span v-if="emp.closing" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                  Fechado {{ emp.closing.closed_at }}
                </span>
                <!-- Botão fechar -->
                <button
                  v-else-if="!isClosed && emp.id !== 'manual'"
                  type="button"
                  :disabled="emp.pendingValue > 0"
                  :title="emp.pendingValue > 0 ? 'Preencha todos os campos \'Para quem foi pago\' antes de fechar' : ''"
                  class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium transition-colors"
                  :class="emp.pendingValue > 0
                    ? 'cursor-not-allowed bg-gray-100 text-gray-400'
                    : 'bg-slate-800 text-white hover:bg-slate-700'"
                  @click="emp.pendingValue === 0 && (providerCloseTarget = emp)"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                  Fechar caixa
                </button>
              </div>
            </div>

            <!-- Linha de distribuição -->
            <div class="grid divide-x divide-gray-100 sm:grid-cols-3">
              <div class="px-5 py-3">
                <p class="text-xs text-gray-400">Total do prestador</p>
                <p class="mt-0.5 text-base font-bold text-gray-900">{{ fmt(emp.totalServices) }}</p>
              </div>
              <div class="px-5 py-3">
                <p class="text-xs text-gray-400">Taxa da casa ({{ houseRate }}%)</p>
                <p class="mt-0.5 text-base font-bold text-violet-600">{{ fmt(emp.taxaCasa) }}</p>
              </div>
              <div class="px-5 py-3">
                <p class="text-xs text-gray-400">Parte do prestador</p>
                <p class="mt-0.5 text-base font-bold text-emerald-600">{{ fmt(emp.partePrestador) }}</p>
              </div>
            </div>

            <!-- Fluxo físico -->
            <div class="grid divide-x divide-gray-100 border-t border-gray-100 sm:grid-cols-3">
              <div class="px-5 py-3 text-sm">
                <p class="text-xs text-gray-400">Pago ao prestador</p>
                <p class="font-medium text-gray-900">{{ fmt(emp.recebidoPeloPrestador) }}</p>
              </div>
              <div class="px-5 py-3 text-sm">
                <p class="text-xs text-gray-400">Pago à casa</p>
                <p class="font-medium text-gray-900">{{ fmt(emp.recebidoPelaCasa) }}</p>
              </div>
              <div class="px-5 py-3 text-sm">
                <p class="text-xs text-gray-400">Pendente</p>
                <p :class="emp.pendingValue > 0 ? 'text-amber-600 font-medium' : 'text-gray-400'">
                  {{ fmt(emp.pendingValue) }}
                </p>
              </div>
            </div>

            <!-- Resultado do acerto -->
            <div
              class="border-t px-5 py-3 flex items-center justify-between"
              :class="emp.pendingValue > 0
                ? 'bg-amber-50 border-amber-200'
                : emp.net > 0
                  ? 'bg-blue-50 border-blue-200'
                  : emp.net < 0
                    ? 'bg-rose-50 border-rose-200'
                    : 'bg-emerald-50 border-emerald-200'"
            >
              <p
                class="text-sm font-semibold"
                :class="emp.pendingValue > 0
                  ? 'text-amber-700'
                  : emp.net > 0
                    ? 'text-blue-700'
                    : emp.net < 0
                      ? 'text-rose-700'
                      : 'text-emerald-700'"
              >
                <template v-if="emp.pendingValue > 0">
                  ⏳ Há {{ fmt(emp.pendingValue) }} pendente — preencha "Para quem foi pago"
                </template>
                <template v-else-if="emp.net > 0">
                  🏠 Casa deve ao prestador
                </template>
                <template v-else-if="emp.net < 0">
                  ⚠️ Prestador deve à casa
                </template>
                <template v-else>
                  ✓ Acertado
                </template>
              </p>
              <p
                v-if="emp.net !== 0 && emp.pendingValue === 0"
                class="text-lg font-bold"
                :class="emp.net > 0 ? 'text-blue-700' : 'text-rose-700'"
              >
                {{ fmt(Math.abs(emp.net)) }}
              </p>
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

    <!-- Modal: resumo do dia -->
    <div v-if="showSummaryModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Resumo do dia</h3>
            <p class="text-xs text-gray-500">{{ formattedDate }}</p>
          </div>
          <button type="button" class="text-gray-400 hover:text-gray-600" @click="showSummaryModal = false">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="overflow-y-auto p-6 space-y-5">

          <!-- Cards de totais -->
          <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
              <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total geral</p>
              <p class="mt-1 text-xl font-bold text-gray-900">{{ fmt(summary.total_value) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
              <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Prestadores</p>
              <p class="mt-1 text-xl font-bold text-emerald-600">{{ fmt(summary.provider_total) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
              <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Casa</p>
              <p class="mt-1 text-xl font-bold text-blue-600">{{ fmt(summary.store_total) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
              <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Taxa ({{ houseRate }}%)</p>
              <p class="mt-1 text-xl font-bold text-violet-600">{{ fmt(summary.house_fee_total) }}</p>
            </div>
          </div>

          <!-- Acerto por prestador -->
          <div v-if="byEmployee.length > 0" class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Acerto por prestador</p>
            <div
              v-for="emp in byEmployee"
              :key="emp.id"
              class="overflow-hidden rounded-xl border border-gray-200 bg-white"
            >
              <div class="flex items-center justify-between bg-gray-50 px-4 py-2.5 border-b border-gray-100">
                <div class="flex items-center gap-2">
                  <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700">
                    {{ emp.name?.[0]?.toUpperCase() }}
                  </div>
                  <span class="text-sm font-semibold text-gray-900">{{ emp.name }}</span>
                  <span class="text-xs text-gray-400">· {{ emp.count }} atend.</span>
                </div>
                <span class="text-sm font-medium text-gray-700">{{ fmt(emp.totalServices) }}</span>
              </div>

              <div class="grid grid-cols-3 divide-x divide-gray-100 text-sm">
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Taxa da casa ({{ houseRate }}%)</p>
                  <p class="font-medium text-violet-600">{{ fmt(emp.taxaCasa) }}</p>
                </div>
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Parte do prestador</p>
                  <p class="font-medium text-emerald-600">{{ fmt(emp.partePrestador) }}</p>
                </div>
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Pendente</p>
                  <p :class="emp.pendingValue > 0 ? 'text-amber-600 font-medium' : 'text-gray-400'">{{ fmt(emp.pendingValue) }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 divide-x divide-gray-100 border-t border-gray-100 text-sm">
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Pago ao prestador</p>
                  <p class="font-medium text-gray-900">{{ fmt(emp.recebidoPeloPrestador) }}</p>
                </div>
                <div class="px-4 py-2.5">
                  <p class="text-xs text-gray-400">Pago à casa</p>
                  <p class="font-medium text-gray-900">{{ fmt(emp.recebidoPelaCasa) }}</p>
                </div>
              </div>

              <div
                class="border-t px-4 py-2.5 flex items-center justify-between text-sm font-semibold"
                :class="emp.pendingValue > 0
                  ? 'bg-amber-50 border-amber-200 text-amber-700'
                  : emp.net > 0  ? 'bg-blue-50 border-blue-200 text-blue-700'
                  : emp.net < 0  ? 'bg-rose-50 border-rose-200 text-rose-700'
                  : 'bg-emerald-50 border-emerald-200 text-emerald-700'"
              >
                <span>
                  {{ emp.pendingValue > 0 ? '⏳ Há pendências'
                    : emp.net > 0  ? '🏠 Casa deve ao prestador'
                    : emp.net < 0  ? '⚠️ Prestador deve à casa'
                    : '✓ Acertado' }}
                </span>
                <span v-if="emp.net !== 0 && emp.pendingValue === 0" class="text-base font-bold">
                  {{ fmt(Math.abs(emp.net)) }}
                </span>
              </div>
            </div>
          </div>

        </div>

        <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4 shrink-0">
          <button
            v-if="isClosed"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="recalculateDay"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Recalcular
          </button>
          <span v-else />
          <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors" @click="showSummaryModal = false">
            Fechar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: fechar caixa do prestador -->
    <div v-if="providerCloseTarget" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Fechar caixa — {{ providerCloseTarget.name }}</h3>
            <p class="text-xs text-gray-500">{{ formattedDate }}</p>
          </div>
          <button type="button" class="text-gray-400 hover:text-gray-600" @click="providerCloseTarget = null">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="space-y-1 p-6">
          <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <span class="text-gray-500">Total de serviços</span>
            <span class="text-right font-medium text-gray-900">{{ fmt(providerCloseTarget.totalServices) }}</span>

            <span class="text-gray-500">Taxa da casa ({{ houseRate }}%)</span>
            <span class="text-right font-medium text-violet-600">{{ fmt(providerCloseTarget.taxaCasa) }}</span>

            <span class="text-gray-500">Parte do prestador</span>
            <span class="text-right font-medium text-emerald-600">{{ fmt(providerCloseTarget.partePrestador) }}</span>

            <span class="col-span-2 border-t border-gray-100 pt-2 text-gray-400 text-xs uppercase tracking-wide">Fluxo físico</span>

            <span class="text-gray-500">Pago ao prestador</span>
            <span class="text-right font-medium text-gray-900">{{ fmt(providerCloseTarget.recebidoPeloPrestador) }}</span>

            <span class="text-gray-500">Pago à casa</span>
            <span class="text-right font-medium text-gray-900">{{ fmt(providerCloseTarget.recebidoPelaCasa) }}</span>
          </div>

          <!-- Resultado do acerto -->
          <div
            class="mt-3 rounded-lg px-4 py-3 text-sm font-semibold flex items-center justify-between"
            :class="providerCloseTarget.net > 0
              ? 'bg-blue-50 text-blue-700'
              : providerCloseTarget.net < 0
                ? 'bg-rose-50 text-rose-700'
                : 'bg-emerald-50 text-emerald-700'"
          >
            <span>
              {{ providerCloseTarget.net > 0 ? '🏠 Casa deve ao prestador'
                : providerCloseTarget.net < 0 ? '⚠️ Prestador deve à casa'
                : '✓ Acertado' }}
            </span>
            <span v-if="providerCloseTarget.net !== 0" class="text-lg font-bold">
              {{ fmt(Math.abs(providerCloseTarget.net)) }}
            </span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
          <button type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors" @click="providerCloseTarget = null">
            Cancelar
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 transition-colors"
            @click="confirmCloseProvider"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            Confirmar fechamento
          </button>
        </div>
      </div>
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
import { vMaska } from 'maska/vue';
import Layout from '@/Layouts/AdminLayout.vue';

const isoToBr = (d) => { if (!d || d.length < 10) return ''; const [y, m, day] = d.substring(0, 10).split('-'); return `${day}/${m}/${y}`; };
const brToIso = (d) => { if (!d || d.length !== 10) return ''; const [dd, mm, yy] = d.split('/'); return `${yy}-${mm}-${dd}`; };

const props = defineProps({
  date:             { type: String, default: null },
  dailyClosing:     { type: Object, default: null },
  appointments:     { type: Array,  default: () => [] },
  entries:          { type: Array,  default: () => [] },
  preview:          { type: Object, default: null },
  settings:         { type: Object, default: () => ({}) },
  providerClosings: { type: Object, default: () => ({}) },
});

const today = isoToBr(new Date().toISOString().split('T')[0]);
const selectedDate        = ref(isoToBr(props.date) || today);
const showAddModal        = ref(false);
const showSummaryModal    = ref(false);
const providerCloseTarget = ref(null); // { id, name, ...emp } — abre modal de confirmação

// Armazena atualizações de paid_to localmente para resposta imediata
const localPaidTo = ref({});

const isClosed = computed(() => props.dailyClosing?.status === 'closed');

const formattedDate = computed(() => {
  if (!props.date) return '';
  return new Date(props.date + 'T00:00:00').toLocaleDateString('pt-BR', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  });
});

const allItems = computed(() => {
  const key = (item) => `${item.type}-${item.id}`;
  const appts = (props.appointments ?? []).map(a => ({
    ...a,
    _sortKey: a.starts_at,
    paid_to: localPaidTo.value[key(a)] !== undefined ? localPaidTo.value[key(a)] : a.paid_to,
  }));
  const ents = (props.entries ?? []).map(e => ({
    ...e,
    _sortKey: e.starts_at,
    paid_to: localPaidTo.value[key(e)] !== undefined ? localPaidTo.value[key(e)] : e.paid_to,
  }));
  return [...appts, ...ents].sort((a, b) => a._sortKey.localeCompare(b._sortKey));
});

const houseRate = computed(() => parseFloat(props.settings?.house_fee_rate ?? 0));

// Calcula client-side para atualizar instantaneamente a cada marcação
const summary = computed(() => {
  if (isClosed.value && props.dailyClosing) {
    return {
      total_value:     parseFloat(props.dailyClosing.total_value ?? 0),
      provider_total:  parseFloat(props.dailyClosing.provider_total ?? 0),
      store_total:     parseFloat(props.dailyClosing.store_total ?? 0),
      house_fee_total: parseFloat(props.dailyClosing.house_fee_total ?? 0),
    };
  }

  let total = 0, providerSum = 0, storeSum = 0;
  for (const item of allItems.value) {
    const base = parseFloat(item.service_value ?? 0);
    total += base;
    if (item.paid_to === 'provider') providerSum += base;
    else if (item.paid_to === 'store') storeSum  += base;
  }
  const feeSum = total * houseRate.value / 100;
  return {
    total_value:     parseFloat(total.toFixed(2)),
    provider_total:  parseFloat(providerSum.toFixed(2)),
    store_total:     parseFloat(storeSum.toFixed(2)),
    house_fee_total: parseFloat(feeSum.toFixed(2)),
  };
});

// Breakdown totais por quem recebeu
const paidToBreakdown = computed(() => {
  let storeVal = 0, providerVal = 0, pendingVal = 0;
  let storeCount = 0, providerCount = 0, pendingCount = 0;
  for (const item of allItems.value) {
    const val = parseFloat(item.service_value ?? 0);
    if (item.paid_to === 'store')         { storeVal += val;    storeCount++;    }
    else if (item.paid_to === 'provider') { providerVal += val; providerCount++; }
    else                                  { pendingVal += val;  pendingCount++;  }
  }
  return { store: storeVal, provider: providerVal, pending: pendingVal,
           storeCount, providerCount, pendingCount };
});

// Acerto por prestador
// Regra: casa recebe house_fee_rate% de cada serviço (se include_house_fee)
//        prestador recebe o restante
// paid_to mostra quem tem o dinheiro fisicamente → determina quem deve quem
const byEmployee = computed(() => {
  const rate = houseRate.value / 100;
  const map = new Map();

  for (const item of allItems.value) {
    const empId   = item.employee_id   ?? 'manual';
    const empName = item.employee_name ?? 'Entrada manual';

    if (!map.has(empId)) {
      map.set(empId, {
        id: empId, name: empName,
        count: 0,
        totalServices:          0,
        taxaCasa:               0, // o que a casa DEVERIA receber
        partePrestador:         0, // o que o prestador DEVERIA receber
        recebidoPeloPrestador:  0, // dinheiro físico com o prestador
        recebidoPelaCasa:       0, // dinheiro físico com a casa
        prestadorDeveCasa:      0, // prestador recebeu mas deve taxa à casa
        casaDevePrestador:      0, // casa recebeu mas deve parte ao prestador
        pendingValue:           0,
      });
    }

    const emp = map.get(empId);
    const val = parseFloat(item.service_value ?? 0);
    const taxa = val * rate;
    const parteProvider = val - taxa;

    emp.count++;
    emp.totalServices  += val;
    emp.taxaCasa       += taxa;
    emp.partePrestador += parteProvider;

    if (item.paid_to === 'provider') {
      emp.recebidoPeloPrestador += val;
      emp.prestadorDeveCasa     += taxa;         // deve repassar a taxa à casa
    } else if (item.paid_to === 'store') {
      emp.recebidoPelaCasa   += val;
      emp.casaDevePrestador  += parteProvider;   // deve repassar o restante ao prestador
    } else {
      emp.pendingValue += val;
    }
  }

  return Array.from(map.values()).map(emp => {
    const closing = emp.id !== 'manual' ? (props.providerClosings[emp.id] ?? null) : null;
    return {
      ...emp,
      net: parseFloat((emp.casaDevePrestador - emp.prestadorDeveCasa).toFixed(2)),
      closing,
    };
  });
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
  const iso = brToIso(selectedDate.value);
  if (!iso) return;
  router.get(`/cash/daily/${iso}`);
}

function updatePaidTo(item, paid_to) {
  // Atualiza localmente de imediato (sem aguardar servidor)
  const key = `${item.type}-${item.id}`;
  localPaidTo.value[key] = paid_to || null;

  const route = item.type === 'appointment'
    ? `/cash/daily/${props.date}/appointments/${item.id}`
    : `/cash/daily/${props.date}/entries/${item.id}`;
  router.patch(route, { paid_to: paid_to || null }, { preserveScroll: true, preserveState: true });
}

function removeEntry(id) {
  if (!confirm('Remover esta entrada?')) return;
  router.delete(`/cash/daily/${props.date}/entries/${id}`);
}

function reopenDay() {
  router.post(`/cash/daily/${props.date}/reopen`, {}, { preserveScroll: true });
}

function recalculateDay() {
  router.post(`/cash/daily/${props.date}/recalculate`, {}, { preserveScroll: true });
}

function confirmClose() {
  if (!confirm(`Fechar o caixa de ${formattedDate.value}? Esta ação não pode ser desfeita.`)) return;
  router.post(`/cash/daily/${props.date}/close`);
}

function confirmCloseProvider() {
  if (!providerCloseTarget.value) return;
  router.post(
    `/cash/daily/${props.date}/providers/${providerCloseTarget.value.id}/close`,
    {},
    { preserveScroll: true, onSuccess: () => { providerCloseTarget.value = null; } }
  );
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
