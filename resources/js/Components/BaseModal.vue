<template>
  <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
    <div class="w-full max-w-2xl rounded bg-white p-6 shadow-lg">
      <button class="float-right text-gray-500" type="button" @click="$emit('close')">✕</button>
      <div class="mt-4">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
