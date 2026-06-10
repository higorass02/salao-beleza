<template>
  <div class="relative" ref="container">
    <!-- Input + ícone -->
    <div class="flex items-center">
      <input
        v-maska="'##/##/####'"
        :value="modelValue"
        type="text"
        inputmode="numeric"
        placeholder="dd/mm/aaaa"
        v-bind="$attrs"
        class="rounded-l-lg rounded-r-none border border-r-0 border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
        @input="$emit('update:modelValue', $event.target.value)"
        @keydown.enter.prevent="open = false"
      />
      <button
        type="button"
        class="flex h-[38px] w-10 shrink-0 items-center justify-center rounded-r-lg border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 hover:text-rose-600 transition-colors focus:outline-none"
        @click="toggle"
        tabindex="-1"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
      </button>
    </div>

    <!-- Calendário flutuante -->
    <Transition
      enter-active-class="transition-all duration-150"
      enter-from-class="opacity-0 -translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition-all duration-100"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 -translate-y-1 scale-95"
    >
      <div
        v-if="open"
        class="absolute left-0 top-full z-50 mt-1 w-72 origin-top-left rounded-xl border border-gray-200 bg-white shadow-xl"
      >
        <!-- Cabeçalho do mês -->
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
          <button
            type="button"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
            @click="prevMonth"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
          </button>

          <button
            type="button"
            class="text-sm font-semibold text-gray-900 hover:text-rose-600 transition-colors capitalize"
            @click="goToday"
            title="Ir para hoje"
          >
            {{ monthLabel }}
          </button>

          <button
            type="button"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
            @click="nextMonth"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>

        <!-- Dias da semana -->
        <div class="grid grid-cols-7 border-b border-gray-100 px-3 py-2">
          <div
            v-for="d in weekDays"
            :key="d"
            class="text-center text-xs font-medium text-gray-400"
          >{{ d }}</div>
        </div>

        <!-- Grade de dias -->
        <div class="grid grid-cols-7 gap-0.5 p-3">
          <!-- Células vazias para alinhar o 1º dia -->
          <div v-for="_ in firstDayOffset" :key="`e${_}`" />

          <button
            v-for="day in daysInMonth"
            :key="day"
            type="button"
            class="flex h-8 w-full items-center justify-center rounded-lg text-sm transition-colors"
            :class="dayClass(day)"
            @click="selectDay(day)"
          >{{ day }}</button>
        </div>

        <!-- Rodapé: atalho para hoje -->
        <div class="border-t border-gray-100 px-3 py-2 text-center">
          <button
            type="button"
            class="text-xs font-medium text-rose-600 hover:text-rose-700 transition-colors"
            @click="selectToday"
          >
            Hoje
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { vMaska } from 'maska/vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
});

const emit      = defineEmits(['update:modelValue']);
const container = ref(null);
const open      = ref(false);

// ── helpers ────────────────────────────────────────────────────────────────
const isoToBr  = (d) => { if (!d || d.length < 10) return ''; const [y, m, day] = d.substring(0, 10).split('-'); return `${day}/${m}/${y}`; };
const brToIso  = (d) => { if (!d || d.length !== 10) return ''; const [dd, mm, yy] = d.split('/'); return `${yy}-${mm}-${dd}`; };
const todayIso = () => new Date().toISOString().split('T')[0];

// ── estado do calendário ───────────────────────────────────────────────────
const viewYear  = ref(new Date().getFullYear());
const viewMonth = ref(new Date().getMonth()); // 0-based

// Inicializa o mês a partir do valor atual
const syncViewToValue = () => {
  const iso = brToIso(props.modelValue);
  if (iso) {
    const d = new Date(iso + 'T00:00:00');
    viewYear.value  = d.getFullYear();
    viewMonth.value = d.getMonth();
  }
};

const weekDays    = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];
const daysInMonth = computed(() => new Date(viewYear.value, viewMonth.value + 1, 0).getDate());
const firstDayOffset = computed(() => new Date(viewYear.value, viewMonth.value, 1).getDay());

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1)
    .toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
);

// Dia selecionado no calendário
const selectedDay = computed(() => {
  const iso = brToIso(props.modelValue);
  if (!iso) return null;
  const d = new Date(iso + 'T00:00:00');
  if (d.getFullYear() === viewYear.value && d.getMonth() === viewMonth.value) return d.getDate();
  return null;
});

const isToday = (day) => {
  const t = new Date();
  return day === t.getDate() && viewMonth.value === t.getMonth() && viewYear.value === t.getFullYear();
};

function dayClass(day) {
  if (day === selectedDay.value) return 'bg-rose-600 text-white font-semibold hover:bg-rose-700';
  if (isToday(day))              return 'border border-rose-300 text-rose-600 font-semibold hover:bg-rose-50';
  return 'text-gray-700 hover:bg-gray-100';
}

// ── ações ──────────────────────────────────────────────────────────────────
function prevMonth() {
  if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value--; }
  else viewMonth.value--;
}

function nextMonth() {
  if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++; }
  else viewMonth.value++;
}

function goToday() {
  viewYear.value  = new Date().getFullYear();
  viewMonth.value = new Date().getMonth();
}

function selectDay(day) {
  const iso = `${viewYear.value}-${String(viewMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  emit('update:modelValue', isoToBr(iso));
  open.value = false;
}

function selectToday() {
  emit('update:modelValue', isoToBr(todayIso()));
  open.value = false;
}

function toggle() {
  if (!open.value) syncViewToValue();
  open.value = !open.value;
}

// Fecha ao clicar fora
function onClickOutside(e) {
  if (container.value && !container.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>
