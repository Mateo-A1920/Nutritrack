<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

async function submit() {
  await auth.register(name.value, email.value, password.value, passwordConfirmation.value)
  router.push({ name: 'dashboard' })
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f4f0] flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
      <div class="mb-10">
        <p class="text-xs font-mono text-zinc-400 tracking-widest uppercase mb-2">NutriTrack</p>
        <h1 class="text-3xl font-semibold text-zinc-900 leading-tight">Crear cuenta</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Nombre</label>
          <input v-model="name" type="text" required class="w-full bg-white border border-zinc-200 rounded-lg px-4 py-3 text-sm outline-none focus:border-zinc-900 transition-colors" />
        </div>
        <div>
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Correo</label>
          <input v-model="email" type="email" required class="w-full bg-white border border-zinc-200 rounded-lg px-4 py-3 text-sm outline-none focus:border-zinc-900 transition-colors" />
        </div>
        <div>
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Contrasena</label>
          <input v-model="password" type="password" required class="w-full bg-white border border-zinc-200 rounded-lg px-4 py-3 text-sm outline-none focus:border-zinc-900 transition-colors" />
        </div>
        <div>
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Confirmar contrasena</label>
          <input v-model="passwordConfirmation" type="password" required class="w-full bg-white border border-zinc-200 rounded-lg px-4 py-3 text-sm outline-none focus:border-zinc-900 transition-colors" />
        </div>

        <p v-if="auth.error" class="text-xs text-red-500">{{ auth.error }}</p>

        <button type="submit" :disabled="auth.loading" class="w-full bg-zinc-900 text-white rounded-lg px-4 py-3 text-sm font-medium hover:bg-zinc-700 transition-colors disabled:opacity-50">
          {{ auth.loading ? 'Cargando...' : 'Registrarse' }}
        </button>
      </form>

      <p class="text-center text-sm text-zinc-400 mt-6">
        Ya tienes cuenta?
        <RouterLink to="/login" class="text-zinc-900 font-medium hover:underline">Iniciar sesion</RouterLink>
      </p>
    </div>
  </div>
</template>