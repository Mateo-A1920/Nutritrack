<script setup lang="ts">
import { useAuthStore } from '@/stores/authStore'
import { useRouter, useRoute } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

async function logout() {
  await auth.logout()
  router.push({ name: 'login' })
}

const nav = [
  { name: 'dashboard', label: 'Nutricion',  path: '/' },
  { name: 'routines',  label: 'Rutinas',    path: '/rutinas' },
  { name: 'progress',  label: 'Progreso',   path: '/progreso' },
]
</script>

<template>
  <div class="min-h-screen bg-[#f5f4f0]">
    <header class="bg-white border-b border-zinc-100 sticky top-0 z-40">
      <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
        <div class="flex items-center gap-8">
          <span class="text-xs font-mono text-zinc-400 tracking-widest uppercase">NutriTrack</span>
          <nav class="flex items-center gap-1">
            <RouterLink
              v-for="item in nav"
              :key="item.name"
              :to="item.path"
              class="px-3 py-1.5 rounded-lg text-sm transition-colors"
              :class="route.name === item.name
                ? 'bg-zinc-900 text-white font-medium'
                : 'text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100'"
            >
              {{ item.label }}
            </RouterLink>
          </nav>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm text-zinc-500 hidden sm:block">{{ auth.user?.name }}</span>
          <button @click="logout" class="text-xs text-zinc-400 hover:text-zinc-700 transition-colors">Salir</button>
        </div>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
      <RouterView />
    </main>
  </div>
</template>