<template>
  <Layout>
    <div class="mx-auto max-w-2xl space-y-6">
      <div class="flex items-center gap-3">
        <Link href="/services" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-700 transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar serviço</h1>
          <p class="mt-0.5 text-sm text-gray-500">{{ service.name }}</p>
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

          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Descrição</label>
            <textarea
              v-model="form.description"
              rows="2"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            />
            <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Preço (R$) <span class="text-red-500">*</span></label>
            <div class="relative mt-1">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">R$</span>
              <input
                v-model="form.price"
                type="number"
                min="0"
                step="0.01"
                class="block w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                :class="{ 'border-red-300': form.errors.price }"
                required
              />
            </div>
            <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Duração <span class="text-red-500">*</span></label>
            <div class="relative mt-1">
              <input
                v-model="form.duration_minutes"
                type="number"
                min="1"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-12 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                :class="{ 'border-red-300': form.errors.duration_minutes }"
                required
              />
              <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">min</span>
            </div>
            <p v-if="form.errors.duration_minutes" class="mt-1 text-xs text-red-600">{{ form.errors.duration_minutes }}</p>
          </div>

          <div class="sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-3">
              <div class="relative">
                <input type="checkbox" v-model="form.include_house_fee" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-violet-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
              </div>
              <div>
                <span class="text-sm font-medium text-gray-700">Cobrar taxa da casa</span>
                <p class="text-xs text-gray-400">Aplica o percentual da casa configurado no admin.</p>
              </div>
            </label>
          </div>

          <div class="sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-3">
              <div class="relative">
                <input type="checkbox" v-model="form.active" class="sr-only peer" />
                <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
              </div>
              <span class="text-sm font-medium text-gray-700">Serviço ativo</span>
            </label>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
          <Link href="/services" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
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
  service: { type: Object, required: true },
});

const form = useForm({
  name:              props.service.name,
  description:       props.service.description ?? '',
  price:             props.service.price,
  duration_minutes:  props.service.duration_minutes,
  active:            Boolean(props.service.active),
  include_house_fee: Boolean(props.service.include_house_fee),
});

function submit() {
  form.put(`/services/${props.service.id}`);
}
</script>
