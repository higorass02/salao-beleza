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
          <h1 class="text-2xl font-bold text-gray-900">Editar funcionário</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ employee.name }}</p>
        </div>
      </div>

      <form class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
        <div class="grid gap-5 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Nome <span class="text-red-500">*</span></label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.name }"
              required
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.email }"
              required
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Telefone</label>
            <input
              v-model="form.phone"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.phone }"
            />
            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
          </div>

          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Função <span class="text-red-500">*</span></label>
            <input
              v-model="form.role"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              :class="{ 'border-red-300': form.errors.role }"
              required
            />
            <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
          </div>

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
            {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
          </button>
        </div>
      </form>
    </div>
  </Layout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  employee: { type: Object, required: true },
});

const form = useForm({
  name: props.employee.name,
  email: props.employee.email ?? '',
  phone: props.employee.phone ?? '',
  role: props.employee.role ?? '',
  active: Boolean(props.employee.active),
});

function submit() {
  form.put(`/employees/${props.employee.id}`);
}
</script>
