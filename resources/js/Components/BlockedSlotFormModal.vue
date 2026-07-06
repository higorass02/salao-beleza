<template>
  <BaseModal :show="show" @close="$emit('close')" max-width="md">
    <div class="p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">
        {{ isAdmin ? 'Bloquear horário' : 'Reservar horário' }}
      </h2>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Funcionário (só admin) -->
        <div v-if="isAdmin">
          <label class="block text-sm font-medium text-gray-700 mb-1">Funcionário</label>
          <select
            v-model="form.employee_id"
            required
            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm"
          >
            <option value="">Selecione…</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
          </select>
          <p v-if="errors.employee_id" class="mt-1 text-xs text-red-600">{{ errors.employee_id }}</p>
        </div>

        <!-- Início -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Início</label>
          <input
            v-model="form.starts_at"
            type="datetime-local"
            required
            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm"
          />
          <p v-if="errors.starts_at" class="mt-1 text-xs text-red-600">{{ errors.starts_at }}</p>
        </div>

        <!-- Fim -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Término</label>
          <input
            v-model="form.ends_at"
            type="datetime-local"
            required
            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm"
          />
          <p v-if="errors.ends_at" class="mt-1 text-xs text-red-600">{{ errors.ends_at }}</p>
        </div>

        <!-- Observação -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Observação</label>
          <input
            v-model="form.notes"
            type="text"
            placeholder="Ex: Folga, Compromisso…"
            maxlength="255"
            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm"
          />
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="$emit('close')"
            class="rounded-xl border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-medium text-white hover:bg-rose-400 disabled:opacity-60"
          >
            {{ submitting ? 'Salvando…' : 'Bloquear horário' }}
          </button>
        </div>
      </form>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import BaseModal from './BaseModal.vue'

const emit = defineEmits(['close', 'saved'])

const props = defineProps({
  show:      { type: Boolean, default: false },
  isAdmin:   { type: Boolean, default: false },
  employees: { type: Array, default: () => [] },
  storeUrl:  { type: String, required: true },
})

const form = ref({ employee_id: '', starts_at: '', ends_at: '', notes: '' })
const errors    = ref({})
const submitting = ref(false)

watch(() => props.show, (v) => {
  if (!v) { form.value = { employee_id: '', starts_at: '', ends_at: '', notes: '' }; errors.value = {} }
})

async function submit() {
  submitting.value = true
  errors.value = {}
  try {
    const payload = {
      starts_at: form.value.starts_at.replace('T', ' '),
      ends_at:   form.value.ends_at.replace('T', ' '),
      notes:     form.value.notes,
    }
    if (props.isAdmin) payload.employee_id = form.value.employee_id

    const { data } = await axios.post(props.storeUrl, payload)
    emit('saved', data)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    }
  } finally {
    submitting.value = false
  }
}
</script>
