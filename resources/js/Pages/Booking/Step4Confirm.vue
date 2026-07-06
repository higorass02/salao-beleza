<template>
  <BookingLayout :step="4">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Confirme seu agendamento</h1>
    <p class="text-gray-500 mb-6">Identifique-se para finalizar</p>

    <!-- Resumo da seleção -->
    <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
      <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Resumo</h2>
      <dl class="grid grid-cols-2 gap-3 text-sm">
        <div>
          <dt class="text-gray-400">Data e horário</dt>
          <dd class="font-medium text-gray-900">{{ formattedDate }}</dd>
        </div>
      </dl>
    </div>

    <!-- Dois caminhos: Google ou Guest -->
    <div class="flex flex-col sm:flex-row sm:items-stretch gap-4 sm:gap-0 mb-6">

      <!-- Google -->
      <div class="flex-1 rounded-2xl border-2 border-gray-200 bg-white p-6 shadow-sm flex flex-col gap-4">
        <div>
          <p class="font-semibold text-gray-900 mb-1">Entrar com Google</p>
          <p class="text-sm text-gray-500">Acesse sua conta Google para identificar-se rapidamente</p>
        </div>
        <a
          :href="googleUrl"
          class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-400 transition-all"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continuar com Google
        </a>
      </div>

      <!-- Divisor único: horizontal no mobile, vertical no desktop -->
      <div class="flex sm:flex-col items-center sm:px-5 py-1 sm:py-0">
        <div class="flex-1 h-px sm:h-full sm:w-px bg-gray-200" />
        <span class="px-3 sm:px-0 sm:py-3 text-xs text-gray-400 shrink-0">ou</span>
        <div class="flex-1 h-px sm:h-full sm:w-px bg-gray-200" />
      </div>

      <!-- Formulário guest -->
      <div class="flex-1 rounded-2xl border-2 border-gray-200 bg-white p-6 shadow-sm">
        <p class="font-semibold text-gray-900 mb-1">Continuar sem conta</p>
        <p class="text-sm text-gray-500 mb-4">Preencha seus dados para finalizar</p>
        <form @submit.prevent="submitGuest" class="flex flex-col gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo <span class="text-rose-500">*</span></label>
            <input
              v-model="form.guest_name"
              type="text"
              required
              placeholder="Seu nome"
              class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm"
            />
            <p v-if="errors.guest_name" class="mt-1 text-xs text-red-600">{{ errors.guest_name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone / WhatsApp <span class="text-rose-500">*</span></label>
            <input
              v-model="form.guest_phone"
              @input="onPhoneInput"
              @blur="phoneTouched = true"
              type="tel"
              inputmode="numeric"
              maxlength="15"
              placeholder="(00) 00000-0000"
              :class="[
                'block w-full rounded-xl border shadow-sm focus:ring-1 text-sm px-3 py-2.5 transition-colors',
                phoneInvalid
                  ? 'border-red-400 focus:border-red-400 focus:ring-red-400'
                  : 'border-gray-300 focus:border-rose-500 focus:ring-rose-500'
              ]"
            />
            <p v-if="phoneInvalid" class="mt-1 text-xs text-red-600">
              {{ phoneErrorMsg }}
            </p>
            <p v-else-if="errors.guest_phone" class="mt-1 text-xs text-red-600">{{ errors.guest_phone }}</p>
          </div>
          <p v-if="errors.starts_at" class="text-xs text-red-600">{{ errors.starts_at }}</p>
          <button
            type="submit"
            :disabled="submitting"
            class="mt-1 rounded-xl bg-rose-500 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-400 disabled:opacity-60 transition-colors"
          >
            {{ submitting ? 'Agendando…' : 'Confirmar agendamento' }}
          </button>
        </form>
      </div>
    </div>
  </BookingLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import BookingLayout from './BookingLayout.vue'

const props = defineProps({
  employee_id: Number,
  service_id:  Number,
  starts_at:   String,
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})

const form         = ref({ guest_name: '', guest_phone: '' })
const submitting   = ref(false)
const phoneTouched = ref(false)

function applyPhoneMask(raw) {
  const digits = raw.replace(/\D/g, '').slice(0, 11)
  if (digits.length <= 2)  return digits.replace(/^(\d{0,2})/, '($1')
  if (digits.length <= 6)  return digits.replace(/^(\d{2})(\d{0,4})/, '($1) $2')
  if (digits.length <= 10) return digits.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3')
  return digits.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3')
}

function onPhoneInput(e) {
  form.value.guest_phone = applyPhoneMask(e.target.value)
}

const phoneDigits  = computed(() => form.value.guest_phone.replace(/\D/g, ''))
const phoneErrorMsg = computed(() => {
  if (!form.value.guest_phone) return 'Telefone obrigatório'
  if (phoneDigits.value.length < 10) return 'Número incompleto — mínimo 10 dígitos'
  return ''
})
const phoneInvalid = computed(() => phoneTouched.value && !!phoneErrorMsg.value)

const formattedDate = computed(() => {
  if (!props.starts_at) return ''
  return new Date(props.starts_at).toLocaleString('pt-BR', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
})

const googleUrl = computed(() => {
  const params = new URLSearchParams({
    service_id:  props.service_id,
    employee_id: props.employee_id,
    starts_at:   props.starts_at,
  })
  return '/auth/client/google?' + params.toString()
})

function submitGuest() {
  phoneTouched.value = true
  if (phoneErrorMsg.value) return
  submitting.value = true
  router.post('/booking', {
    employee_id: props.employee_id,
    service_id:  props.service_id,
    starts_at:   props.starts_at,
    guest_name:  form.value.guest_name,
    guest_phone: form.value.guest_phone,
  }, {
    onFinish: () => { submitting.value = false },
  })
}
</script>
