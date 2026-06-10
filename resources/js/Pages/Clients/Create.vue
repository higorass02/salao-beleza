<template>
  <component :is="layout">
    <div class="mx-auto max-w-2xl space-y-6">
      <div class="flex items-center gap-3">
        <Link
          href="/clients"
          class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Novo cliente</h1>
          <p class="mt-0.5 text-sm text-gray-500">Preencha os dados do cliente</p>
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

          <!-- Apelido -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Apelido <span class="text-gray-400 font-normal">(opcional)</span></label>
            <input
              v-model="form.apelido"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.apelido }"
              placeholder="Como o cliente prefere ser chamado"
            />
            <p class="mt-1 text-xs text-gray-500">Se preenchido, aparece no autocomplete em vez do telefone</p>
            <p v-if="form.errors.apelido" class="mt-1 text-xs text-red-600">{{ form.errors.apelido }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700">E-mail</label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.email }"
              placeholder="exemplo@email.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Telefone -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Telefone</label>
            <input
              v-maska="phoneMask"
              v-model="form.phone"
              type="text"
              inputmode="numeric"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.phone }"
              placeholder="(00) 00000-0000"
            />
            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
          </div>

          <!-- Aniversário -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Aniversário <span class="text-gray-400 font-normal">(opcional)</span></label>
            <div class="mt-1 flex items-center gap-2">
              <select
                v-model="form.birth_day"
                class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                :class="{ 'border-red-300': form.errors.birth_day }"
              >
                <option value="">Dia</option>
                <option v-for="d in 31" :key="d" :value="d">{{ d }}</option>
              </select>
              <span class="text-gray-400">/</span>
              <select
                v-model="form.birth_month"
                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                :class="{ 'border-red-300': form.errors.birth_month }"
              >
                <option value="">Mês</option>
                <option v-for="(name, idx) in months" :key="idx + 1" :value="idx + 1">{{ name }}</option>
              </select>
            </div>
            <p v-if="form.errors.birth_day || form.errors.birth_month" class="mt-1 text-xs text-red-600">
              {{ form.errors.birth_day || form.errors.birth_month }}
            </p>
          </div>

          <!-- Observações -->
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Observações</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              placeholder="Informações adicionais..."
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
          <Link href="/clients" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
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
            {{ form.processing ? 'Salvando...' : 'Salvar cliente' }}
          </button>
        </div>
      </form>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { vMaska } from 'maska/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CollaboratorLayout from '@/Layouts/CollaboratorLayout.vue';

const page = usePage();
const layout = computed(() =>
  page.props.auth?.user?.is_collaborator ? CollaboratorLayout : AdminLayout
);

const phoneMask = ['(##) ####-####', '(##) #####-####'];

const months = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

const form = useForm({
  name:        '',
  apelido:     '',
  email:       '',
  phone:       '',
  notes:       '',
  birth_day:   '',
  birth_month: '',
});

function submit() {
  form.post('/clients');
}
</script>
