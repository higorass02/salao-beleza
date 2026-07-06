<template>
  <CollaboratorLayout>
    <div class="px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Meus Bloqueios</h1>
          <p class="mt-0.5 text-sm text-gray-500">Gerencie seus horários reservados</p>
        </div>
        <button
          @click="showForm = true"
          class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-400 transition-colors shadow-sm"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Reservar Horário
        </button>
      </div>

      <!-- Tabela -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table v-if="blockedSlots.length" class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Início</th>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Término</th>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Observação</th>
              <th class="px-6 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="slot in blockedSlots" :key="slot.id">
              <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ formatDt(slot.starts_at) }}</td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDt(slot.ends_at) }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ slot.notes ?? '—' }}</td>
              <td class="whitespace-nowrap px-6 py-4 text-right">
                <button
                  @click="confirmDelete(slot)"
                  class="text-sm text-red-600 hover:text-red-800 font-medium"
                >
                  Remover
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="flex flex-col items-center justify-center py-16 text-center text-gray-400">
          <svg class="mb-3 h-10 w-10 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
          </svg>
          <p>Nenhum bloqueio cadastrado.</p>
        </div>
      </div>
    </div>

    <!-- Modal: novo bloqueio -->
    <BlockedSlotFormModal
      :show="showForm"
      :is-admin="false"
      store-url="/collaborator/blocked-slots"
      @close="showForm = false"
      @saved="onSaved"
    />

    <!-- Modal: conflitos -->
    <BlockedSlotConflictModal
      :show="showConflicts"
      :conflicts="conflicts"
      @close="showConflicts = false"
    />
  </CollaboratorLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CollaboratorLayout from '@/Layouts/CollaboratorLayout.vue'
import BlockedSlotFormModal from '@/Components/BlockedSlotFormModal.vue'
import BlockedSlotConflictModal from '@/Components/BlockedSlotConflictModal.vue'

const props = defineProps({
  blockedSlots: Array,
})

const showForm      = ref(false)
const showConflicts = ref(false)
const conflicts     = ref([])

function formatDt(dt) {
  if (!dt) return ''
  return new Date(dt).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function onSaved(data) {
  showForm.value = false
  if (data.conflicts?.length) {
    conflicts.value = data.conflicts
    showConflicts.value = true
  }
  router.reload({ preserveScroll: true })
}

function confirmDelete(slot) {
  if (!confirm('Remover este bloqueio?')) return
  router.delete(`/collaborator/blocked-slots/${slot.id}`, { preserveScroll: true })
}
</script>
