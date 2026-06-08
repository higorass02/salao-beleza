<template>
  <Layout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Agenda</h1>
        <p class="mt-1 text-sm text-gray-500">Clique em um horário para criar um agendamento</p>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">

        <!-- Filtros: busca de cliente (esquerda) + prestadores (direita) -->
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

          <!-- Autocomplete de cliente -->
          <div class="relative w-64 shrink-0">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
              v-model="clientQuery"
              type="text"
              placeholder="Buscar cliente..."
              class="block w-full rounded-lg border border-gray-300 py-2 pl-9 pr-8 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
            />
            <button
              v-if="clientQuery"
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              @click="clearClientSearch"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
            <ul
              v-if="clientSuggestions.length"
              class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
            >
              <li
                v-for="client in clientSuggestions"
                :key="client.id"
                class="cursor-pointer px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors"
                @mousedown.prevent="selectClient(client)"
              >
                <span class="font-medium text-gray-900">{{ client.name }}</span>
                <span v-if="client.phone || client.email" class="ml-2 text-gray-500">{{ client.phone || client.email }}</span>
              </li>
            </ul>
          </div>

          <!-- Pills de prestador -->
          <div class="flex flex-wrap justify-end gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
              :class="selectedEmployeeId === null
                ? 'bg-gray-900 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              @click="selectEmployee(null)"
            >
              Todos
            </button>
            <button
              v-for="(employee, index) in employees"
              :key="employee.id"
              type="button"
              class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium transition-colors"
              :class="selectedEmployeeId === employee.id
                ? 'text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              :style="selectedEmployeeId === employee.id
                ? { backgroundColor: palette[index % palette.length].bg }
                : {}"
              @click="selectEmployee(employee.id)"
            >
              <span
                class="h-2 w-2 rounded-full shrink-0"
                :style="{ backgroundColor: palette[index % palette.length].bg }"
              />
              {{ employee.name }}
            </button>
          </div>

        </div>

        <div ref="calendarContainer" />
      </div>
    </div>

    <AppointmentModal
      v-if="showModal"
      :date="selectedDate"
      :time="selectedTime"
      :services="services"
      :employees="employees"
      @close="showModal = false"
    />

    <AppointmentDetailModal
      v-if="selectedAppointment"
      :appointment="selectedAppointment"
      @close="selectedAppointment = null"
    />
  </Layout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Layout from '@/Layouts/AdminLayout.vue';
import AppointmentModal from './AppointmentModal.vue';
import AppointmentDetailModal from './AppointmentDetailModal.vue';

const props = defineProps({
  appointments:       { type: Array,  default: () => [] },
  services:           { type: Array,  default: () => [] },
  employees:          { type: Array,  default: () => [] },
  businessHoursStart: { type: String, default: '08:00' },
  businessHoursEnd:   { type: String, default: '20:00' },
});

const palette = [
  { bg: '#f43f5e', border: '#e11d48' },
  { bg: '#8b5cf6', border: '#7c3aed' },
  { bg: '#3b82f6', border: '#2563eb' },
  { bg: '#10b981', border: '#059669' },
  { bg: '#f59e0b', border: '#d97706' },
  { bg: '#ec4899', border: '#db2777' },
  { bg: '#06b6d4', border: '#0891b2' },
  { bg: '#f97316', border: '#ea580c' },
];

function employeeColor(employeeId) {
  const index = props.employees.findIndex((e) => e.id === employeeId);
  return palette[index % palette.length] ?? palette[0];
}

function mapToEvents(appointments) {
  return appointments.map((a) => {
    const color = employeeColor(a.employee_id);
    return {
      id:              a.id,
      title:           `${a.client?.name ?? ''} — ${a.service?.name ?? ''}`,
      start:           a.starts_at,
      end:             a.ends_at,
      backgroundColor: color.bg,
      borderColor:     color.border,
    };
  });
}

// --- estado ---
const showModal           = ref(false);
const selectedDate        = ref(null);
const selectedTime        = ref(null);
const calendarContainer   = ref(null);
const selectedAppointment = ref(null);
const selectedEmployeeId  = ref(null);

// autocomplete de cliente
const clientQuery       = ref('');
const clientSuggestions = ref([]);
const selectedClientId  = ref(null);

let calendarInstance = null;
let clientDebounce   = null;

// --- filtros ---
function getFiltered() {
  let result = props.appointments;
  if (selectedEmployeeId.value !== null) {
    result = result.filter((a) => a.employee_id === selectedEmployeeId.value);
  }
  if (selectedClientId.value !== null) {
    result = result.filter((a) => a.client_id === selectedClientId.value);
  }
  return result;
}

function applyFilters() {
  if (!calendarInstance) return;
  calendarInstance.removeAllEvents();
  calendarInstance.addEventSource(mapToEvents(getFiltered()));
}

function selectEmployee(id) {
  selectedEmployeeId.value = id;
  applyFilters();
}

// --- autocomplete de cliente ---
watch(clientQuery, (val) => {
  clearTimeout(clientDebounce);
  if (!val || val.length < 2) {
    clientSuggestions.value = [];
    if (selectedClientId.value !== null) {
      selectedClientId.value = null;
      applyFilters();
    }
    return;
  }
  clientDebounce = setTimeout(() => fetchClients(val), 300);
});

async function fetchClients(q) {
  try {
    const res = await fetch(`/clients/search?q=${encodeURIComponent(q)}`);
    if (res.ok) clientSuggestions.value = await res.json();
  } catch {
    clientSuggestions.value = [];
  }
}

function selectClient(client) {
  clientQuery.value       = client.name;
  clientSuggestions.value = [];
  selectedClientId.value  = client.id;
  applyFilters();
}

function clearClientSearch() {
  clientQuery.value       = '';
  clientSuggestions.value = [];
  selectedClientId.value  = null;
  applyFilters();
}

// --- calendário ---
onMounted(async () => {
  const { Calendar }      = await import('@fullcalendar/core');
  const timeGridPlugin    = (await import('@fullcalendar/timegrid')).default;
  const interactionPlugin = (await import('@fullcalendar/interaction')).default;
  const ptBrLocale        = (await import('@fullcalendar/core/locales/pt-br')).default;

  calendarInstance = new Calendar(calendarContainer.value, {
    plugins:     [timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale:      ptBrLocale,
    headerToolbar: {
      left:   'prev,next today',
      center: 'title',
      right:  'timeGridWeek,timeGridDay',
    },
    slotMinTime:   `${props.businessHoursStart}:00`,
    slotMaxTime:   `${props.businessHoursEnd}:00`,
    allDaySlot:    false,
    expandRows:    false,
    contentHeight: 'auto',
    events: mapToEvents(props.appointments),
    eventClick(info) {
      const id = Number(info.event.id);
      selectedAppointment.value = props.appointments.find((a) => a.id === id) ?? null;
    },
    dateClick(info) {
      selectedDate.value = info.dateStr.split('T')[0];
      selectedTime.value = (info.dateStr.split('T')[1] ?? '').slice(0, 5) || '08:00';
      showModal.value = true;
    },
  });

  calendarInstance.render();
});
</script>
