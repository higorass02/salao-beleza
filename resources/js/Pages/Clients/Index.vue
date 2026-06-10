<template>
  <component :is="layout">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Clientes</h1>
          <p class="mt-1 text-sm text-gray-500">{{ clients.total }} cliente(s) cadastrado(s)</p>
        </div>
        <Link
          href="/clients/create"
          class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Novo cliente
        </Link>
      </div>

      <!-- Busca server-side -->
      <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nome, apelido ou telefone..."
          class="block w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
        />
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm overflow-x-auto">
        <div v-if="clients.data.length === 0" class="px-6 py-16 text-center">
          <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
          </svg>
          <p class="mt-2 text-sm font-medium text-gray-900">Nenhum cliente encontrado</p>
          <p class="mt-1 text-sm text-gray-500">Tente outro termo de busca ou cadastre um novo cliente.</p>
        </div>

        <table v-else class="min-w-full">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50">
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Nome</th>
              <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Apelido</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Telefone</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                    {{ client.name?.[0]?.toUpperCase() }}
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ client.name }}</span>
                </div>
              </td>
              <td class="hidden sm:table-cell px-6 py-4 text-sm text-gray-600">{{ client.apelido || '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ client.phone || '—' }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link
                    :href="`/clients/${client.id}/edit`"
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
                    @click="destroy(client.id)"
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
          :links="clients.links"
          :from="clients.from"
          :to="clients.to"
          :total="clients.total"
        />
      </div>
    </div>
  </component>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CollaboratorLayout from '@/Layouts/CollaboratorLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const page = usePage();
const layout = computed(() =>
  page.props.auth?.user?.is_collaborator ? CollaboratorLayout : AdminLayout
);

const props = defineProps({
  clients: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.q ?? '');

let searchTimer = null;
watch(search, (val) => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    router.get('/clients', { q: val || undefined }, { preserveState: true, replace: true });
  }, 350);
});

function destroy(id) {
  if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
  router.delete(`/clients/${id}`);
}
</script>
