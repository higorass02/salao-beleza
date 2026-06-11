<template>
  <Layout>
    <div class="mx-auto max-w-2xl space-y-6">
      <div class="flex items-center gap-3">
        <Link href="/employees" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Novo funcionário</h1>
          <p class="mt-0.5 text-sm text-gray-500">Preencha os dados do funcionário</p>
        </div>
      </div>

      <form class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
        <div class="grid gap-5 sm:grid-cols-2">

          <!-- Nome -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Nome <span class="text-red-500">*</span></label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.name }"
              placeholder="Nome completo"
              required
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700">E-mail <span class="text-red-500">*</span></label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.email }"
              placeholder="exemplo@email.com"
              required
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Telefone -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Telefone</label>
            <input
              v-maska="phoneMask"
              :value="form.phone"
              @input="form.phone = $event.target.value"
              type="text"
              inputmode="numeric"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.phone }"
              placeholder="(00) 00000-0000"
            />
            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
          </div>

          <!-- Função -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Função <span class="text-red-500">*</span></label>
            <input
              v-model="form.role"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.role }"
              placeholder="Ex: Cabeleireiro, Manicure..."
              required
            />
            <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
          </div>

          <!-- Aniversário -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Aniversário <span class="text-gray-400 font-normal">(opcional)</span></label>
            <div class="mt-1 flex items-center gap-2">
              <select
                v-model="form.birth_day"
                class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              >
                <option value="">Dia</option>
                <option v-for="d in 31" :key="d" :value="d">{{ d }}</option>
              </select>
              <span class="text-gray-400">/</span>
              <select
                v-model="form.birth_month"
                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              >
                <option value="">Mês</option>
                <option v-for="(name, idx) in months" :key="idx + 1" :value="idx + 1">{{ name }}</option>
              </select>
            </div>
          </div>

          <!-- Ativo -->
          <div class="sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-3">
              <div class="relative">
                <input type="checkbox" v-model="form.active" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
              </div>
              <span class="text-sm font-medium text-gray-700">Funcionário ativo</span>
            </label>
          </div>

          <!-- Taxa de serviço -->
          <div class="sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-3">
              <div class="relative">
                <input type="checkbox" v-model="form.charges_house_fee" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
              </div>
              <div>
                <span class="text-sm font-medium text-gray-700">Cobra taxa de serviço (casa)</span>
                <p class="text-xs text-gray-500">Quando ativo, a taxa da casa é descontada nos fechamentos</p>
              </div>
            </label>
          </div>

          <!-- Notificação diária -->
          <div class="sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-3">
              <div class="relative">
                <input type="checkbox" v-model="form.notify_appointments" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
              </div>
              <div>
                <span class="text-sm font-medium text-gray-700">Receber notificação diária de agendamentos</span>
                <p class="text-xs text-gray-500">E-mail às 09h com os clientes do dia (o colaborador também pode desativar nas próprias configurações)</p>
              </div>
            </label>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
          <Link href="/employees" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50 transition-colors"
          >
            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ form.processing ? 'Salvando...' : 'Salvar funcionário' }}
          </button>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';
import Layout from '@/Layouts/AdminLayout.vue';

const phoneMask = { mask: ['(##) ####-####', '(##) #####-####'] };

const months = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

const form = useForm({
  name:                 '',
  email:                '',
  phone:                '',
  role:                 '',
  active:               true,
  charges_house_fee:    true,
  notify_appointments:  true,
  birth_day:            '',
  birth_month:          '',
});

function submit() {
  form.post('/employees');
}
</script>
