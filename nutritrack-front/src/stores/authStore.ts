import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApiFetch } from '@/composables/useApiFetch'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('nutritrack_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  const { post, get } = useApiFetch()

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const data = await post<{ user: User; token: string }>('/login', { email, password })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('nutritrack_token', data.token)
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Error al iniciar sesion'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function register(name: string, email: string, password: string, password_confirmation: string) {
    loading.value = true
    error.value = null
    try {
      const data = await post<{ user: User; token: string }>('/register', {
        name, email, password, password_confirmation,
      })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('nutritrack_token', data.token)
    } catch (e: unknown) {
      error.value = e instanceof Error ? e.message : 'Error al registrar'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await post('/logout', {})
    } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('nutritrack_token')
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      user.value = await get<User>('/me')
    } catch {
      logout()
    }
  }

  return { user, token, loading, error, isAuthenticated, login, register, logout, fetchMe }
})