<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header público -->
    <header class="bg-white shadow-sm">
      <div class="mx-auto max-w-3xl px-4 py-4 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
            </svg>
          </div>
          <span class="font-bold text-gray-900" style="font-family: Georgia, serif;">
            Espaço <span class="text-rose-500">Delas</span>
          </span>
        </a>
        <span class="text-sm text-gray-500">Agendamento Online</span>
      </div>
    </header>

    <!-- Stepper -->
    <div v-if="step" class="bg-white border-b border-gray-100">
      <div class="mx-auto max-w-3xl px-4 py-3">
        <div class="flex items-center gap-2">
          <template v-for="(label, i) in steps" :key="i">
            <div class="flex items-center gap-1.5">
              <div
                :class="[
                  'flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold',
                  i + 1 < step ? 'bg-rose-500 text-white' :
                  i + 1 === step ? 'bg-rose-100 text-rose-700 ring-2 ring-rose-500' :
                  'bg-gray-100 text-gray-400'
                ]"
              >
                <svg v-if="i + 1 < step" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span v-else>{{ i + 1 }}</span>
              </div>
              <span :class="['text-xs hidden sm:block', i + 1 === step ? 'text-rose-700 font-medium' : 'text-gray-400']">
                {{ label }}
              </span>
            </div>
            <div v-if="i < steps.length - 1" class="flex-1 h-px bg-gray-200" />
          </template>
        </div>
      </div>
    </div>

    <!-- Conteúdo -->
    <main class="mx-auto max-w-3xl px-4 py-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
defineProps({
  step: { type: Number, default: null },
})

const steps = ['Serviço', 'Profissional', 'Horário', 'Confirmação']
</script>
