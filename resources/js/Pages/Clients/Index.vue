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
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="client in clients.data"
              :key="client.id"
              class="hover:bg-gray-50"
              :class="{ 'opacity-60': !client.active }"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                    :class="client.active ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'"
                  >
                    {{ client.name?.[0]?.toUpperCase() }}
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ client.name }}</span>
                </div>
              </td>
              <td class="hidden sm:table-cell px-6 py-4 text-sm text-gray-600">{{ client.apelido || '—' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ client.phone || '—' }}</td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                  :class="client.active
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                    : 'bg-red-50 text-red-700 ring-red-600/20'"
                >
                  {{ client.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
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

                  <!-- Reativar -->
                  <button
                    v-if="!client.active"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-1.5 text-xs font-medium text-emerald-600 hover:border-emerald-300 hover:bg-emerald-50 transition-colors"
                    @click="activate(client)"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Reativar
                  </button>

                  <!-- Desativar -->
                  <button
                    v-else
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:border-red-300 hover:bg-red-50 transition-colors"
                    @click="confirmDeactivate(client)"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Desativar
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

    <!-- Modal de confirmação de desativação -->
    <div
      v-if="deactivatingClient"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4"
    >
      <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
        <div class="flex items-start gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-gray-900">Desativar cliente?</h3>
            <p class="mt-1 text-sm text-gray-500">
              <strong>{{ deactivatingClient.name }}</strong> será desativado e todos os
              agendamentos futuros serão cancelados automaticamente. O admin receberá um e-mail
              com a lista dos agendamentos cancelados.
            </p>
            <p class="mt-2 text-xs text-gray-400">Esta ação pode ser revertida reativando o cliente.</p>
          </div>
        </div>
        <div class="mt-5 flex justify-end gap-3">
          <button
            type="button"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="deactivatingClient = null"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors"
            @click="doDeactivate"
          >
            Desativar
          </button>
        </div>
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

const deactivatingClient = ref(null);

function confirmDeactivate(client) {
  deactivatingClient.value = client;
}

function doDeactivate() {
  router.delete(`/clients/${deactivatingClient.value.id}`, {
    onFinish: () => { deactivatingClient.value = null; },
  });
}

function activate(client) {
  router.put(`/clients/${client.id}/activate`);
}
</script>
