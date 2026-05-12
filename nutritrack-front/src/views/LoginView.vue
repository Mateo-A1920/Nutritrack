<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')

async function submit() {
  await auth.login(email.value, password.value)
  router.push({ name: 'dashboard' })
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f4f0] flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
      <div class="mb-10">
        <p class="text-xs font-mono text-zinc-400 tracking-widest uppercase mb-2">NutriTrack</p>
        <h1 class="text-3xl font-semibold text-zinc-900 leading-tight">Bienvenido</h1>
        <p class="text-sm text-zinc-500 mt-1">Inicia sesion para continuar</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Correo</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full bg-white border border-zinc-200 rounded-lg px-4 py-3 text-sm text-zinc-900 outline-none focus:border-zinc-900 transition-colors"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Contrasena</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full bg-white border border-zinc-200 rounded-lg px-4 py-3 text-sm text-zinc-900 outline-none focus:border-zinc-900 transition-colors"
          />
        </div>

        <p v-if="auth.error" class="text-xs text-red-500">{{ auth.error }}</p>

        <button
          type="submit"
          :disabled="auth.loading"
          class="w-full bg-zinc-900 text-white rounded-lg px-4 py-3 text-sm font-medium hover:bg-zinc-700 transition-colors disabled:opacity-50"
        >
          {{ auth.loading ? 'Cargando...' : 'Iniciar sesion' }}
        </button>
      </form>

      <p class="text-center text-sm text-zinc-400 mt-6">
        Sin cuenta?
        <RouterLink to="/register" class="text-zinc-900 font-medium hover:underline">Registrarse</RouterLink>
      </p>
    </div>
  </div>
</template>