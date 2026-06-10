<template>
  <div v-if="links.length > 3" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <div class="hidden sm:block">
      <p class="text-sm text-gray-700">
        Mostrando <span class="font-medium">{{ from }}</span> a <span class="font-medium">{{ to }}</span>
        de <span class="font-medium">{{ total }}</span> resultado(s)
      </p>
    </div>
    <div class="flex flex-1 justify-between gap-1 sm:justify-end">
      <Link
        v-for="link in links"
        :key="link.label"
        :href="link.url ?? ''"
        :class="[
          'relative inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
          link.active
            ? 'bg-rose-600 text-white'
            : link.url
              ? 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'
              : 'cursor-default bg-white text-gray-300 ring-1 ring-inset ring-gray-200',
        ]"
        :preserve-scroll="true"
        as="button"
        :disabled="!link.url"
        v-html="link.label"
      />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: { type: Array, default: () => [] },
  from:  { type: Number, default: 0 },
  to:    { type: Number, default: 0 },
  total: { type: Number, default: 0 },
});
</script>
