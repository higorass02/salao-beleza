<template>
  <Layout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Serviços</h1>
          <p class="mt-1 text-sm text-gray-500">{{ services.total }} serviço(s) cadastrado(s)</p>
        </div>
        <Link
          href="/services/create"
          class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Novo serviço
        </Link>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm overflow-x-auto">
        <div v-if="services.data.length === 0" class="px-6 py-16 text-center">
          <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
          </svg>
          <p class="mt-2 text-sm font-medium text-gray-900">Nenhum serviço cadastrado</p>
          <p class="mt-1 text-sm text-gray-500">Cadastre os serviços oferecidos pelo salão.</p>
        </div>

        <table v-else class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Serviço</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Preço</th>
              <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Duração</th>
              <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="service in services.data" :key="service.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ service.name }}</p>
                  <p v-if="service.description" class="text-xs text-gray-500 truncate max-w-xs">{{ service.description }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                R$ {{ Number(service.price).toFixed(2) }}
              </td>
              <td class="hidden sm:table-cell px-6 py-4 text-sm text-gray-600">{{ service.duration_minutes }} min</td>
              <td class="hidden sm:table-cell px-6 py-4">
                <span
                  :class="service.active
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                    : 'bg-gray-100 text-gray-600 ring-gray-500/10'"
                  class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                >
                  {{ service.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link
                    :href="`/services/${service.id}/edit`"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-gray-300 hover:text-gray-900 transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    Editar
                  </Link>
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:border-red-300 hover:bg-red-50 transition-colors"
                    @click="destroy(service.id)"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Excluir
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination
          :links="services.links"
          :from="services.from"
          :to="services.to"
          :total="services.total"
        />
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
  services: { type: Object, required: true },
});

function destroy(id) {
  if (!confirm('Tem certeza que deseja excluir este serviço?')) return;
  router.delete(`/services/${id}`);
}
</script>
