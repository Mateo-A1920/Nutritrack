const BASE_URL = 'http://localhost:8000/api'

function getToken(): string | null {
  return localStorage.getItem('nutritrack_token')
}

interface FetchOptions extends RequestInit {
  body?: BodyInit | null
}

async function apiFetch<T>(endpoint: string, options: FetchOptions = {}): Promise<T> {
  const token = getToken()

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(options.headers as Record<string, string> || {}),
  }

  const response = await fetch(`${BASE_URL}${endpoint}`, {
    ...options,
    headers,
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Error de red' }))
    throw new Error(error.message || `HTTP ${response.status}`)
  }

  if (response.status === 204) return {} as T

  return response.json()
}

export function useApiFetch() {
  return {
    get: <T>(endpoint: string) => apiFetch<T>(endpoint, { method: 'GET' }),
    post: <T>(endpoint: string, body: unknown) =>
      apiFetch<T>(endpoint, { method: 'POST', body: JSON.stringify(body) }),
    put: <T>(endpoint: string, body: unknown) =>
      apiFetch<T>(endpoint, { method: 'PUT', body: JSON.stringify(body) }),
    del: <T>(endpoint: string) => apiFetch<T>(endpoint, { method: 'DELETE' }),
  }
}