import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApiFetch } from '@/composables/useApiFetch'
import type { BodyMeasurement, WaterLog, UserGoal } from '@/types'

export const useProgressStore = defineStore('progress', () => {
  const measurements = ref<BodyMeasurement[]>([])
  const waterLog = ref<WaterLog>({ glasses: 0, log_date: '' })
  const goals = ref<UserGoal>({ calories_goal: 2000, water_glasses_goal: 8 })
  const loading = ref(false)

  const { get, post, del } = useApiFetch()

  const latestMeasurement = computed(() => measurements.value[0] ?? null)

  const bmi = computed(() => {
    const m = latestMeasurement.value
    if (!m?.weight_kg || !m?.height_cm) return null
    const h = m.height_cm / 100
    return (m.weight_kg / (h * h)).toFixed(1)
  })

  async function fetchMeasurements() {
    loading.value = true
    try { measurements.value = await get<BodyMeasurement[]>('/measurements') }
    finally { loading.value = false }
  }

  async function addMeasurement(payload: Omit<BodyMeasurement, 'id' | 'user_id'>) {
    const m = await post<BodyMeasurement>('/measurements', payload)
    measurements.value.unshift(m)
    return m
  }

  async function deleteMeasurement(id: number) {
    await del(`/measurements/${id}`)
    measurements.value = measurements.value.filter(x => x.id !== id)
  }

  async function fetchWater(date: string) {
    waterLog.value = await get<WaterLog>(`/water/${date}`)
  }

  async function setWater(date: string, glasses: number) {
    waterLog.value = await post<WaterLog>('/water', { log_date: date, glasses })
  }

  async function fetchGoals() {
    goals.value = await get<UserGoal>('/goals')
  }

  async function updateGoals(payload: Partial<UserGoal>) {
    goals.value = await post<UserGoal>('/goals', payload)
  }

  return {
    measurements, waterLog, goals, loading,
    latestMeasurement, bmi,
    fetchMeasurements, addMeasurement, deleteMeasurement,
    fetchWater, setWater, fetchGoals, updateGoals,
  }
})