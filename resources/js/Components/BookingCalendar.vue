<template>
  <div>
    <!-- Grid: calendário + painel lateral (desktop) -->
    <div class="grid gap-5 lg:grid-cols-[1fr_290px]">

      <!-- ── Calendário ── -->
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">

        <!-- Navegação de mês -->
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
          <button
            @click="prevMonth"
            :disabled="!canGoPrev"
            class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-25"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
          </button>

          <span class="text-base font-semibold text-gray-900 capitalize select-none">
            {{ monthLabel }}
          </span>

          <button
            @click="nextMonth"
            class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>

        <div class="p-4 sm:p-5">
          <!-- Nomes dos dias -->
          <div class="grid grid-cols-7 mb-2">
            <div
              v-for="name in dayNames"
              :key="name"
              class="text-center text-xs font-semibold uppercase tracking-wide text-gray-400 py-1 select-none"
            >
              {{ name }}
            </div>
          </div>

          <!-- Células dos dias -->
          <div class="grid grid-cols-7 gap-1">
            <template v-for="(day, i) in calendarDays" :key="i">
              <div v-if="!day" />
              <button
                v-else
                @click="selectDay(day)"
                :disabled="isPast(day)"
                :class="[
                  'relative flex aspect-square w-full items-center justify-center rounded-xl text-sm font-medium transition-all select-none',
                  isPast(day)
                    ? 'cursor-not-allowed text-gray-200'
                    : isSelected(day)
                    ? 'bg-rose-500 text-white shadow-md ring-4 ring-rose-100'
                    : isToday(day)
                    ? 'ring-2 ring-rose-400 text-rose-600 hover:bg-rose-50'
                    : 'text-gray-700 hover:bg-rose-50 hover:text-rose-600 cursor-pointer'
                ]"
              >
                {{ day.getDate() }}
                <span
                  v-if="isToday(day) && !isSelected(day)"
                  class="absolute bottom-1.5 left-1/2 -translate-x-1/2 h-1 w-1 rounded-full bg-rose-400"
                />
              </button>
            </template>
          </div>
        </div>

        <p v-if="!selectedDay" class="lg:hidden pb-4 text-center text-xs text-gray-400">
          Toque em um dia para ver os horários disponíveis
        </p>
      </div>

      <!-- ── Painel lateral de horários (desktop) ── -->
      <Transition name="fade-right">
        <div
          v-if="selectedDay"
          class="hidden lg:block rounded-2xl border border-gray-200 bg-white shadow-sm p-5 self-start lg:sticky lg:top-6"
        >
          <!-- Cabeçalho -->
          <div class="mb-4 pb-3 border-b border-gray-100 flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50">
              <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Horários</p>
              <p class="text-sm font-semibold text-gray-900 capitalize">{{ formattedDay }}</p>
            </div>
          </div>

          <!-- Carregando -->
          <div v-if="loadingSlots" class="flex flex-col items-center gap-3 py-8 text-gray-400">
            <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm">Buscando horários…</span>
          </div>

          <!-- Sem horários -->
          <div v-else-if="!loadingSlots && slots.length === 0" class="flex flex-col items-center gap-2 py-8 text-center">
            <svg class="h-8 w-8 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
            <p class="text-sm font-medium text-gray-500">Nenhum horário disponível</p>
            <p class="text-xs text-gray-400">Tente selecionar outro dia</p>
          </div>

          <!-- Grid de slots -->
          <template v-else>
            <p class="mb-3 text-xs text-gray-400">
              {{ slots.length }} horário{{ slots.length !== 1 ? 's' : '' }} disponível{{ slots.length !== 1 ? 'is' : '' }}
            </p>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="slot in slots"
                :key="slot"
                @click="selectSlot(slot)"
                class="rounded-xl border-2 border-gray-200 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:border-rose-400 hover:bg-rose-50 hover:text-rose-600 active:scale-95"
              >
                {{ slot }}
              </button>
            </div>
          </template>
        </div>
      </Transition>
    </div>

    <!-- ── Bottom Sheet mobile ── -->
    <Teleport to="body">
      <Transition name="sheet">
        <div
          v-if="selectedDay"
          class="lg:hidden fixed inset-0 z-40 flex flex-col justify-end"
        >
          <!-- Overlay -->
          <div class="absolute inset-0 bg-black/50" @click="closeSheet" />

          <!-- Sheet -->
          <div class="relative flex flex-col bg-white rounded-t-3xl max-h-[85dvh] min-h-[55dvh]">

            <!-- Handle -->
            <div class="flex shrink-0 justify-center pt-3 pb-1 cursor-pointer" @click="closeSheet">
              <div class="h-1 w-10 rounded-full bg-gray-300" />
            </div>

            <!-- Cabeçalho fixo -->
            <div class="shrink-0 flex items-center justify-between px-5 pb-3 pt-2 border-b border-gray-100">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50">
                  <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Horários</p>
                  <p class="text-sm font-semibold text-gray-900 capitalize">{{ formattedDay }}</p>
                </div>
              </div>
              <button
                @click="closeSheet"
                class="flex h-8 w-8 items-center justify-center rounded-xl border border-gray-200 text-gray-400 hover:text-gray-600"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Conteúdo rolável -->
            <div class="flex-1 overflow-y-auto px-5 py-4">

              <!-- Carregando -->
              <div v-if="loadingSlots" class="flex flex-col items-center gap-3 py-10 text-gray-400">
                <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-sm">Buscando horários…</span>
              </div>

              <!-- Sem horários -->
              <div v-else-if="!loadingSlots && slots.length === 0" class="flex flex-col items-center gap-2 py-10 text-center">
                <svg class="h-10 w-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <p class="text-sm font-medium text-gray-500">Nenhum horário disponível</p>
                <p class="text-xs text-gray-400">Tente selecionar outro dia</p>
              </div>

              <!-- Grid de slots -->
              <template v-else>
                <p class="mb-3 text-xs text-gray-400">
                  {{ slots.length }} horário{{ slots.length !== 1 ? 's' : '' }} disponível{{ slots.length !== 1 ? 'is' : '' }}
                </p>
                <div class="grid grid-cols-3 gap-3">
                  <button
                    v-for="slot in slots"
                    :key="slot"
                    @click="selectSlot(slot)"
                    class="rounded-xl border-2 border-gray-200 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-rose-400 hover:bg-rose-50 hover:text-rose-600 active:scale-95"
                  >
                    {{ slot }}
                  </button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── Modal de confirmação ── -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 flex items-end justify-center sm:items-center p-4"
        >
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false" />

          <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gradient-to-r from-rose-500 to-rose-400 px-6 py-4">
              <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                <p class="font-semibold text-white">Resumo do agendamento</p>
              </div>
              <button
                @click="showModal = false"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-white/70 transition-colors hover:bg-white/10 hover:text-white"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Corpo -->
            <div class="p-6 space-y-4">
              <div class="flex items-center gap-4 rounded-xl bg-gray-50 px-4 py-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100">
                  <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="text-xs text-gray-400 uppercase tracking-wider">Serviço</p>
                  <p class="font-semibold text-gray-900 truncate">{{ service.name }}</p>
                </div>
                <span class="ml-auto text-xs text-gray-400 shrink-0">{{ service.duration_minutes }} min</span>
              </div>

              <div class="flex items-center gap-4 rounded-xl bg-gray-50 px-4 py-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-rose-600 text-white font-bold">
                  {{ employee.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="text-xs text-gray-400 uppercase tracking-wider">Profissional</p>
                  <p class="font-semibold text-gray-900">{{ employee.name }}</p>
                </div>
              </div>

              <div class="flex items-center gap-4 rounded-xl bg-gray-50 px-4 py-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100">
                  <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400 uppercase tracking-wider">Data e horário</p>
                  <p class="font-semibold text-gray-900 capitalize">{{ formattedDay }}</p>
                  <p class="text-sm text-gray-500">{{ selectedSlot }} – {{ endTime }}</p>
                </div>
              </div>

              <div class="flex items-center justify-between rounded-xl border-2 border-rose-100 bg-rose-50 px-4 py-3">
                <p class="text-sm font-medium text-rose-700">Valor total</p>
                <p class="text-2xl font-bold text-rose-600">{{ formattedPrice }}</p>
              </div>
            </div>

            <!-- Rodapé -->
            <div class="flex gap-3 border-t border-gray-100 px-6 py-4">
              <button
                @click="showModal = false"
                class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50"
              >
                Cancelar
              </button>
              <button
                @click="confirm"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-rose-500 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-rose-400 active:scale-[.98]"
              >
                Confirmar
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const emit = defineEmits(['confirm'])

const props = defineProps({
  employeeId: { type: Number, required: true },
  serviceId:  { type: Number, required: true },
  service:    { type: Object, required: true },
  employee:   { type: Object, required: true },
})

const dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

const today = new Date()
today.setHours(0, 0, 0, 0)

const currentMonthDate = ref(new Date(today.getFullYear(), today.getMonth(), 1))

const selectedDay  = ref(null)
const selectedSlot = ref(null)
const slots        = ref([])
const loadingSlots = ref(false)
const showModal    = ref(false)

// Travar scroll da página quando sheet ou modal estiver aberto
watch([selectedDay, showModal], ([day, modal]) => {
  document.body.style.overflow = (day || modal) ? 'hidden' : ''
}, { flush: 'post' })

// ── Navegação de mês ──

const canGoPrev = computed(() => {
  const d = currentMonthDate.value
  return d.getFullYear() > today.getFullYear() || d.getMonth() > today.getMonth()
})

function prevMonth() {
  const d = currentMonthDate.value
  currentMonthDate.value = new Date(d.getFullYear(), d.getMonth() - 1, 1)
  resetSelection()
}

function nextMonth() {
  const d = currentMonthDate.value
  currentMonthDate.value = new Date(d.getFullYear(), d.getMonth() + 1, 1)
  resetSelection()
}

function resetSelection() {
  selectedDay.value  = null
  selectedSlot.value = null
  slots.value        = []
}

const monthLabel = computed(() =>
  currentMonthDate.value.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
)

// ── Grade do calendário ──

const calendarDays = computed(() => {
  const year  = currentMonthDate.value.getFullYear()
  const month = currentMonthDate.value.getMonth()
  const startWeekday = new Date(year, month, 1).getDay()
  const daysInMonth  = new Date(year, month + 1, 0).getDate()

  const days = []
  for (let i = 0; i < startWeekday; i++) days.push(null)
  for (let d = 1; d <= daysInMonth; d++)  days.push(new Date(year, month, d))
  return days
})

function isPast(day)     { return day < today }
function isToday(day)    { return day.getTime() === today.getTime() }
function isSelected(day) { return selectedDay.value?.toDateString() === day.toDateString() }

// ── Selecionar dia e buscar slots ──

async function selectDay(day) {
  if (isPast(day)) return
  selectedDay.value  = day
  selectedSlot.value = null
  slots.value        = []
  loadingSlots.value = true

  const y = day.getFullYear()
  const m = String(day.getMonth() + 1).padStart(2, '0')
  const d = String(day.getDate()).padStart(2, '0')

  try {
    const { data } = await axios.get('/booking/slots', {
      params: { employee_id: props.employeeId, service_id: props.serviceId, date: `${y}-${m}-${d}` },
    })
    slots.value = data
  } catch {
    slots.value = []
  } finally {
    loadingSlots.value = false
  }
}

function closeSheet() {
  selectedDay.value  = null
  selectedSlot.value = null
  slots.value        = []
}

function selectSlot(slot) {
  selectedSlot.value = slot
  showModal.value    = true
}

// ── Valores derivados ──

const formattedDay = computed(() => {
  if (!selectedDay.value) return ''
  return selectedDay.value.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long' })
})

const endTime = computed(() => {
  if (!selectedSlot.value) return ''
  const [h, m] = selectedSlot.value.split(':').map(Number)
  const total  = h * 60 + m + props.service.duration_minutes
  return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`
})

const formattedPrice = computed(() => {
  if (!props.service.price) return 'A consultar'
  return `R$ ${Number(props.service.price).toFixed(2).replace('.', ',')}`
})

// ── Confirmar ──

function confirm() {
  showModal.value = false
  const day = selectedDay.value
  const y   = day.getFullYear()
  const m   = String(day.getMonth() + 1).padStart(2, '0')
  const d   = String(day.getDate()).padStart(2, '0')
  emit('confirm', { startsAt: `${y}-${m}-${d} ${selectedSlot.value}` })
}
</script>

<style scoped>
/* Painel lateral desktop */
.fade-right-enter-active,
.fade-right-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-right-enter-from,
.fade-right-leave-to {
  opacity: 0;
  transform: translateX(14px);
}

/* Bottom sheet mobile */
.sheet-enter-active { transition: opacity 0.25s ease; }
.sheet-leave-active { transition: opacity 0.2s ease; }
.sheet-enter-active .relative,
.sheet-leave-active .relative {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.sheet-enter-from,
.sheet-leave-to { opacity: 0; }
.sheet-enter-from .relative,
.sheet-leave-to .relative { transform: translateY(100%); }

/* Modal de confirmação */
.modal-enter-active,
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-active .relative,
.modal-leave-active .relative { transition: opacity 0.2s ease, transform 0.2s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
.modal-enter-from .relative,
.modal-leave-to .relative { opacity: 0; transform: translateY(16px) scale(0.98); }
</style>
