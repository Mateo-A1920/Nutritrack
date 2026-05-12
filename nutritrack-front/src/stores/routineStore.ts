import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApiFetch } from '@/composables/useApiFetch'
import type { Routine, WorkoutSession, ExerciseLog } from '@/types'

export const useRoutineStore = defineStore('routine', () => {
  const routines  = ref<Routine[]>([])
  const sessions  = ref<WorkoutSession[]>([])
  const activeSession = ref<WorkoutSession | null>(null)
  const exerciseLogs  = ref<ExerciseLog[]>([])
  const loading   = ref(false)

  const { get, post, put, del } = useApiFetch()

  async function fetchRoutines() {
    loading.value = true
    try { routines.value = await get<Routine[]>('/routines') }
    finally { loading.value = false }
  }

  async function fetchSessions() {
    sessions.value = await get<WorkoutSession[]>('/sessions')
  }

  async function fetchSessionDetail(id: number) {
    const s = await get<WorkoutSession>(`/sessions/${id}`)
    activeSession.value = s
    exerciseLogs.value = s.exercise_logs ?? []
    return s
  }

  async function createRoutine(payload: Omit<Routine, 'id' | 'user_id'>) {
    const r = await post<Routine>('/routines', payload)
    routines.value.unshift(r)
    return r
  }

  async function updateRoutine(id: number, payload: Partial<Routine>) {
    const r = await put<Routine>(`/routines/${id}`, payload)
    const idx = routines.value.findIndex(x => x.id === id)
    if (idx !== -1) routines.value[idx] = r
    return r
  }

  async function deleteRoutine(id: number) {
    await del(`/routines/${id}`)
    routines.value = routines.value.filter(x => x.id !== id)
  }

  async function logSession(payload: {
    routine_id: number
    session_date: string
    duration_minutes?: number
    notes?: string
  }) {
    const s = await post<WorkoutSession>('/sessions', payload)
    sessions.value.unshift(s)
    return s
  }

  async function deleteSession(id: number) {
    await del(`/sessions/${id}`)
    sessions.value = sessions.value.filter(x => x.id !== id)
  }

  async function addExerciseLog(payload: Omit<ExerciseLog, 'id'>) {
    const log = await post<ExerciseLog>('/exercise-logs', payload)
    exerciseLogs.value.push(log)
    return log
  }

  async function updateExerciseLog(id: number, payload: Partial<ExerciseLog>) {
    const updated = await put<ExerciseLog>(`/exercise-logs/${id}`, payload)
    const idx = exerciseLogs.value.findIndex(x => x.id === id)
    if (idx !== -1) exerciseLogs.value[idx] = updated
    return updated
  }

  async function deleteExerciseLog(id: number) {
    await del(`/exercise-logs/${id}`)
    exerciseLogs.value = exerciseLogs.value.filter(x => x.id !== id)
  }

  async function getExerciseStats(name: string) {
    return get<ExerciseLog[]>(`/exercise-stats/${encodeURIComponent(name)}`)
  }

  return {
    routines, sessions, activeSession, exerciseLogs, loading,
    fetchRoutines, fetchSessions, fetchSessionDetail,
    createRoutine, updateRoutine, deleteRoutine,
    logSession, deleteSession,
    addExerciseLog, updateExerciseLog, deleteExerciseLog, getExerciseStats,
  }
})