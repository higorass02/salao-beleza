<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-20 bg-black/60 lg:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-slate-900',
        'transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      ]"
    >
      <!-- Logo -->
      <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500">
          <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-bold leading-tight text-white">Salão de Beleza</p>
          <p class="text-xs leading-tight text-slate-400">Painel Admin</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
        <Link href="/dashboard" :class="navClass('/dashboard')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
          </svg>
          Dashboard
        </Link>

        <Link href="/appointments" :class="navClass('/appointments')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
          </svg>
          Agenda
        </Link>

        <Link href="/clients" :class="navClass('/clients')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
          </svg>
          Clientes
        </Link>

        <Link href="/employees" :class="navClass('/employees')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          Funcionários
        </Link>

        <Link href="/services" :class="navClass('/services')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
          </svg>
          Serviços
        </Link>

        <!-- Separator -->
        <div class="my-3 border-t border-slate-800" />
        <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Caixa</p>

        <Link href="/cash/daily" :class="navClass('/cash/daily')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
          </svg>
          Fechamento Diário
        </Link>

        <Link href="/weekly" :class="navClass('/weekly')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
          </svg>
          Fechamento Semanal
        </Link>

        <!-- Separator -->
        <div class="my-3 border-t border-slate-800" />

        <Link href="/settings" :class="navClass('/settings')" @click="sidebarOpen = false">
          <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
          Configurações
        </Link>
      </nav>

      <!-- User footer -->
      <div class="shrink-0 border-t border-slate-800 p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rose-500 text-sm font-semibold text-white">
            {{ initials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-white">{{ page.props.auth?.user?.name }}</p>
            <p class="text-xs text-slate-400">Administrador</p>
          </div>
          <Link
            href="/logout"
            method="post"
            as="button"
            type="button"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
            title="Sair"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
            </svg>
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content wrapper -->
    <div class="flex min-h-screen flex-col lg:pl-64">
      <!-- Top header -->
      <header class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 lg:hidden"
          @click="sidebarOpen = !sidebarOpen"
        >
          <svg v-if="!sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
          <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
        <div class="flex-1" />
        <!-- Mobile: show brand name -->
        <span class="text-sm font-semibold text-gray-700 lg:hidden">Salão de Beleza</span>
        <div class="flex-1 lg:hidden" />
      </header>

      <!-- Flash messages -->
      <div v-if="flash.success || flash.error" class="px-4 pt-4 sm:px-6 lg:px-8">
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 -translate-y-1"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-1"
        >
          <div
            v-if="flash.success"
            class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
          >
            <svg class="h-5 w-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ flash.success }}
          </div>
        </Transition>
        <Transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 -translate-y-1"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-1"
        >
          <div
            v-if="flash.error"
            class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            {{ flash.error }}
          </div>
        </Transition>
      </div>

      <!-- Page content -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarOpen = ref(false);

const initials = computed(() => {
  const name = page.props.auth?.user?.name ?? '';
  return name
    .split(' ')
    .filter(Boolean)
    .map((w) => w[0].toUpperCase())
    .slice(0, 2)
    .join('');
});

const flash = computed(() => page.props.flash ?? {});

function isActive(path) {
  if (path === '/') return page.url === '/' || page.url === '';
  return page.url.startsWith(path);
}

function navClass(path) {
  const base = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors';
  return isActive(path)
    ? `${base} bg-slate-700 text-white`
    : `${base} text-slate-400 hover:bg-slate-800 hover:text-slate-200`;
}
</script>
