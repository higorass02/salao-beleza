<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-md space-y-8">
      <!-- Logo -->
      <div class="text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500 shadow-lg">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
          </svg>
        </div>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">
          {{ mustChange ? 'Crie sua senha' : 'Alterar senha' }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ mustChange
            ? 'Seu acesso foi criado com uma senha temporária. Defina uma senha pessoal para continuar.'
            : 'Informe sua senha atual e escolha uma nova.' }}
        </p>
      </div>

      <!-- Aviso de troca obrigatória -->
      <div v-if="mustChange" class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <span>Por segurança, é necessário criar uma senha pessoal antes de continuar usando o sistema.</span>
      </div>

      <!-- Card -->
      <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <form class="space-y-5" @submit.prevent="submit">

          <!-- Senha atual -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ mustChange ? 'Senha temporária (recebida por e-mail)' : 'Senha atual' }}
            </label>
            <div class="relative mt-1">
              <input
                v-model="form.current_password"
                :type="showCurrent ? 'text' : 'password'"
                autocomplete="current-password"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-10 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.current_password }"
                placeholder="••••••••"
                required
              />
              <button
                type="button"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                tabindex="-1"
                @click="showCurrent = !showCurrent"
              >
                <svg v-if="!showCurrent" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <p v-if="form.errors.current_password" class="mt-1 text-xs text-red-600">{{ form.errors.current_password }}</p>
          </div>

          <!-- Nova senha -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nova senha</label>
            <div class="relative mt-1">
              <input
                v-model="form.password"
                :type="showNew ? 'text' : 'password'"
                autocomplete="new-password"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-10 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password }"
                placeholder="Mínimo 8 caracteres"
                required
              />
              <button
                type="button"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                tabindex="-1"
                @click="showNew = !showNew"
              >
                <svg v-if="!showNew" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
          </div>

          <!-- Confirmar nova senha -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
              class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"
              placeholder="Repita a nova senha"
              required
            />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50 transition-colors"
          >
            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            {{ form.processing ? 'Salvando...' : (mustChange ? 'Definir senha e entrar' : 'Salvar nova senha') }}
          </button>
        </form>
      </div>

      <!-- Link para voltar (apenas quando não é troca obrigatória) -->
      <p v-if="!mustChange" class="text-center text-xs text-gray-500">
        <Link href="/dashboard" class="text-rose-600 hover:text-rose-700">← Voltar</Link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  mustChange: { type: Boolean, default: false },
});

const showCurrent = ref(false);
const showNew     = ref(false);

const form = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
});

function submit() {
  form.put('/change-password', {
    onFinish: () => form.reset('current_password', 'password', 'password_confirmation'),
  });
}
</script>
