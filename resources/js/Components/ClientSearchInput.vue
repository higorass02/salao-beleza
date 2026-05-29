<template>
  <div class="relative">
    <input
      v-model="query"
      type="text"
      class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
      placeholder="Buscar por nome, e-mail ou telefone..."
    />
    <ul
      v-if="results.length"
      class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
    >
      <li
        v-for="client in results"
        :key="client.id"
        class="cursor-pointer px-4 py-2.5 text-sm hover:bg-gray-50 transition-colors"
        @mousedown.prevent="select(client)"
      >
        <span class="font-medium text-gray-900">{{ client.name }}</span>
        <span class="ml-2 text-gray-500">{{ client.email || client.phone }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Object, default: null },
});
const emit = defineEmits(['update:modelValue']);

const query = ref(props.modelValue?.name ?? '');
const results = ref([]);

let debounceTimer = null;

watch(query, (val) => {
  clearTimeout(debounceTimer);
  if (!val || val.length < 2) {
    results.value = [];
    return;
  }
  debounceTimer = setTimeout(() => fetchResults(val), 300);
});

async function fetchResults(q) {
  try {
    const res = await fetch(`/clients/search?q=${encodeURIComponent(q)}`);
    if (res.ok) results.value = await res.json();
  } catch {
    results.value = [];
  }
}

function select(client) {
  emit('update:modelValue', client);
  query.value = client.name;
  results.value = [];
}
</script>
