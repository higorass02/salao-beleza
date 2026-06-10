<template>
  <Layout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Minha agenda</h1>
          <p class="mt-1 text-sm text-gray-500">Clique em um horário para criar um agendamento</p>
        </div>
        <Link
          href="/collaborator/appointments/create"
          class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Novo agendamento
        </Link>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <div ref="calendarContainer" />
      </div>
    </div>

    <!-- Detail modal -->
    <div
      v-if="selectedAppointment"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4"
      @click.self="selectedAppointment = null"
    >
      <div class="w-full max-w-sm rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-900">Detalhes do agendamento</h2>
          <button
            type="button"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            @click="selectedAppointment = null"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="space-y-3 p-6">
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Cliente</p>
            <p class="mt-0.5 text-sm font-medium text-gray-900">{{ selectedAppointment.client?.name }}</p>
          </div>
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Serviço</p>
            <p class="mt-0.5 text-sm text-gray-900">{{ selectedAppointment.service?.name }}</p>
          </div>
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Início</p>
            <p class="mt-0.5 text-sm text-gray-900">{{ formatDatetime(selectedAppointment.starts_at) }}</p>
          </div>
          <div v-if="selectedAppointment.notes">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Observações</p>
            <p class="mt-0.5 text-sm text-gray-900">{{ selectedAppointment.notes }}</p>
          </div>
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Status</p>
            <span
              class="mt-0.5 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
              :class="statusClass(selectedAppointment.status)"
            >
              {{ statusLabel(selectedAppointment.status) }}
            </span>
          </div>
        </div>
        <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4">
          <button
            type="button"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            @click="selectedAppointment = null"
          >
            Fechar
          </button>
          <Link
            :href="`/collaborator/appointments/${selectedAppointment.id}/edit`"
            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 transition-colors"
          >
            Editar
          </Link>
        </div>
      </div>
    </div>

    <!-- Create appointment modal (clicked on calendar slot) -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4"
      @click.self="showCreateModal = false"
    >
      <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          <div>
            <h2 class="text-base font-semibold text-gray-900">Novo agendamento</h2>
            <p class="text-xs text-gray-500">{{ formattedSelectedDate }}</p>
          </div>
          <button
            type="button"
            class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            @click="showCreateModal = false"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form class="space-y-4 p-6" @submit.prevent="submitCreate">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Data</label>
              <input
                type="date"
                :value="createForm.starts_at_date"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-gray-50 focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                readonly
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Horário</label>
              <input
                v-model="createForm.starts_at_time"
                type="time"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Serviço <span class="text-red-500">*</span></label>
            <select
              v-model="createForm.service_id"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              required
            >
              <option value="">Selecione o serviço</option>
              <option v-for="s in services" :key="s.id" :value="s.id">
                {{ s.name }} — R$ {{ Number(s.price).toFixed(2) }} ({{ s.duration_minutes }} min)
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
            <ClientSearchInput v-model="selectedClient" />
            <p v-if="clientError" class="mt-1 text-xs text-red-600">{{ clientError }}</p>
          </div>
          <div v-if="createFormError" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
            {{ createFormError }}
          </div>
          <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
            <button
              type="button"
              class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
              @click="showCreateModal = false"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="createProcessing"
              class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50 transition-colors"
            >
              <svg v-if="createProcessing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              {{ createProcessing ? 'Agendando...' : 'Agendar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/CollaboratorLayout.vue';
import ClientSearchInput from '@/Components/ClientSearchInput.vue';

const props = defineProps({
  appointments:       { type: Array,  default: () => [] },
  services:           { type: Array,  default: () => [] },
  employees:          { type: Array,  default: () => [] },
  myEmployeeId:       { type: Number, default: null },
  businessHoursStart: { type: String, default: '08:00' },
  businessHoursEnd:   { type: String, default: '20:00' },
});

const calendarContainer   = ref(null);
const selectedAppointment = ref(null);
const showCreateModal     = ref(false);
const selectedClient      = ref(null);
const clientError         = ref('');
const createProcessing    = ref(false);
const createFormError     = ref('');

const createForm = reactive({
  starts_at_date: '',
  starts_at_time: '',
  service_id:     '',
});

const formattedSelectedDate = computed(() => {
  if (!createForm.starts_at_date) return '';
  return new Date(createForm.starts_at_date + 'T00:00:00').toLocaleDateString('pt-BR', {
    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
  });
});

watch(selectedClient, () => { clientError.value = ''; });

function mapToEvents(appointments) {
  return appointments.map((a) => ({
    id:              a.id,
    title:           `${a.client?.name ?? ''} — ${a.service?.name ?? ''}`,
    start:           a.starts_at,
    end:             a.ends_at,
    backgroundColor: '#f43f5e',
    borderColor:     '#e11d48',
  }));
}

function formatDatetime(val) {
  if (!val) return '';
  return new Date(val).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function statusLabel(status) {
  return { scheduled: 'Agendado', done: 'Concluído', canceled: 'Cancelado' }[status] ?? status;
}

function statusClass(status) {
  return {
    scheduled: 'bg-blue-100 text-blue-700',
    done:      'bg-green-100 text-green-700',
    canceled:  'bg-red-100 text-red-700',
  }[status] ?? 'bg-gray-100 text-gray-700';
}

function submitCreate() {
  if (!selectedClient.value) {
    clientError.value = 'Selecione um cliente';
    return;
  }
  createProcessing.value = true;
  createFormError.value  = '';

  router.post('/collaborator/appointments', {
    client_id:   selectedClient.value.id,
    service_id:  createForm.service_id,
    starts_at:   `${createForm.starts_at_date} ${createForm.starts_at_time}:00`,
  }, {
    onSuccess: () => { showCreateModal.value = false; createProcessing.value = false; },
    onError:   (errors) => {
      createFormError.value  = Object.values(errors)[0] ?? 'Erro ao salvar';
      createProcessing.value = false;
    },
  });
}

onMounted(async () => {
  const { Calendar }      = await import('@fullcalendar/core');
  const timeGridPlugin    = (await import('@fullcalendar/timegrid')).default;
  const interactionPlugin = (await import('@fullcalendar/interaction')).default;
  const ptBrLocale        = (await import('@fullcalendar/core/locales/pt-br')).default;

  const calendar = new Calendar(calendarContainer.value, {
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
      createForm.starts_at_date = info.dateStr.split('T')[0];
      createForm.starts_at_time = (info.dateStr.split('T')[1] ?? '').slice(0, 5) || '08:00';
      selectedClient.value      = null;
      createForm.service_id     = '';
      clientError.value         = '';
      showCreateModal.value     = true;
    },
  });

  calendar.render();
});
</script>
