<template>
  <Layout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Agenda</h1>
        <p class="mt-1 text-sm text-gray-500">Clique em um horário para criar um agendamento</p>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
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
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Layout from '@/Layouts/AdminLayout.vue';
import AppointmentModal from './AppointmentModal.vue';

const props = defineProps({
  appointments: { type: Array, default: () => [] },
  services: { type: Array, default: () => [] },
  employees: { type: Array, default: () => [] },
});

const showModal = ref(false);
const selectedDate = ref(null);
const selectedTime = ref(null);
const calendarContainer = ref(null);

onMounted(async () => {
  const { Calendar } = await import('@fullcalendar/core');
  const timeGridPlugin = (await import('@fullcalendar/timegrid')).default;
  const interactionPlugin = (await import('@fullcalendar/interaction')).default;

  const calendar = new Calendar(calendarContainer.value, {
    plugins: [timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: 'pt-br',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'timeGridWeek,timeGridDay',
    },
    slotMinTime: '07:00:00',
    slotMaxTime: '21:00:00',
    allDaySlot: false,
    events: props.appointments.map((a) => ({
      id: a.id,
      title: `${a.client?.name ?? ''} — ${a.service?.name ?? ''}`,
      start: a.starts_at,
      end: a.ends_at,
      backgroundColor: '#f43f5e',
      borderColor: '#e11d48',
    })),
    dateClick(info) {
      selectedDate.value = info.dateStr.split('T')[0];
      selectedTime.value = (info.dateStr.split('T')[1] ?? '').slice(0, 5) || '08:00';
      showModal.value = true;
    },
  });

  calendar.render();
});
</script>
