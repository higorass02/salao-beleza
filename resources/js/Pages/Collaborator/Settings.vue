<template>
  <Layout>
    <div class="mx-auto max-w-2xl space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
        <p class="mt-1 text-sm text-gray-500">Preferências pessoais da sua conta</p>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Notificações</h2>

        <div class="mt-4 space-y-4">
          <!-- Toggle notificações gerais -->
          <div class="flex items-start justify-between gap-4 rounded-lg border border-gray-100 p-4">
            <div>
              <p class="text-sm font-medium text-gray-900">Notificações por e-mail</p>
              <p class="mt-0.5 text-xs text-gray-500">
                Quando ativado, você recebe um e-mail todos os dias às 09h com
                a lista de clientes agendados para o dia — desde que o estabelecimento
                esteja aberto e você tenha atendimentos marcados.
              </p>
            </div>
            <label class="flex shrink-0 cursor-pointer items-center">
              <div class="relative">
                <input
                  type="checkbox"
                  v-model="form.notifications_enabled"
                  class="sr-only peer"
                  @change="submit"
                />
                <div class="h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-rose-500 transition-colors" />
                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5" />
              </div>
            </label>
          </div>

          <p class="text-xs text-gray-400">
            Obs: o administrador também pode ativar ou desativar esta notificação
            individualmente pelo cadastro de funcionários.
          </p>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/CollaboratorLayout.vue';

const props = defineProps({
  notifications_enabled: { type: Boolean, default: true },
});

const form = useForm({
  notifications_enabled: props.notifications_enabled,
});

function submit() {
  form.put('/collaborator/settings', { preserveScroll: true });
}
</script>
