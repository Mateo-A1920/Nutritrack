import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApiFetch } from '@/composables/useApiFetch'
import type { DailyLog, FoodEntry, MealType, DailyLogResponse, NutritionTotals } from '@/types'

export const useNutriStore = defineStore('nutri', () => {
  const currentLog = ref<DailyLog | null>(null)
  const entries = ref<FoodEntry[]>([])
  const loading = ref(false)
  const selectedDate = ref<string>(new Date().toLocaleDateString('en-CA'))

  const { get, post, del, put } = useApiFetch()

  const mealTypes: MealType[] = ['desayuno', 'almuerzo', 'cena', 'aperitivo']

  const entriesByMeal = computed(() => {
    const grouped: Record<MealType, FoodEntry[]> = {
      desayuno: [], almuerzo: [], cena: [], aperitivo: [],
    }
    for (const entry of entries.value) {
      grouped[entry.meal_type].push(entry)
    }
    return grouped
  })

  const totals = computed<NutritionTotals>(() =>
    entries.value.reduce(
      (acc, e) => ({
        calories: acc.calories + Number(e.calories),
        carbs:    acc.carbs    + Number(e.carbs),
        fats:     acc.fats     + Number(e.fats),
        protein:  acc.protein  + Number(e.protein),
        sodium:   acc.sodium   + Number(e.sodium),
        sugar:    acc.sugar    + Number(e.sugar),
      }),
      { calories: 0, carbs: 0, fats: 0, protein: 0, sodium: 0, sugar: 0 }
    )
  )

  const recentFoods = computed(() => {
    const seen = new Map<string, FoodEntry>()
    for (const e of [...entries.value].reverse()) {
      if (!seen.has(e.name.toLowerCase())) seen.set(e.name.toLowerCase(), e)
    }
    return [...seen.values()].slice(0, 10)
  })

  function mealTotals(meal: MealType): NutritionTotals {
    return entriesByMeal.value[meal].reduce(
      (acc, e) => ({
        calories: acc.calories + Number(e.calories),
        carbs:    acc.carbs    + Number(e.carbs),
        fats:     acc.fats     + Number(e.fats),
        protein:  acc.protein  + Number(e.protein),
        sodium:   acc.sodium   + Number(e.sodium),
        sugar:    acc.sugar    + Number(e.sugar),
      }),
      { calories: 0, carbs: 0, fats: 0, protein: 0, sodium: 0, sugar: 0 }
    )
  }

  async function loadDay(date: string) {
    loading.value = true
    selectedDate.value = date
    try {
      const data = await get<DailyLogResponse>(`/logs/${date}`)
      currentLog.value = data.log
      entries.value = data.entries
    } finally {
      loading.value = false
    }
  }

  async function ensureLog() {
    if (!currentLog.value) {
      const log = await post<DailyLog>('/logs', { log_date: selectedDate.value })
      currentLog.value = log
    }
    return currentLog.value!
  }

  async function addEntry(payload: Omit<FoodEntry, 'id' | 'daily_log_id'>) {
    const log = await ensureLog()
    const entry = await post<FoodEntry>('/entries', { ...payload, daily_log_id: log.id })
    entries.value.push(entry)
  }

  async function removeEntry(id: number) {
    await del(`/entries/${id}`)
    entries.value = entries.value.filter(e => e.id !== id)
  }

  async function updateEntry(id: number, payload: Partial<FoodEntry>) {
    const updated = await put<FoodEntry>(`/entries/${id}`, payload)
    const idx = entries.value.findIndex(e => e.id === id)
    if (idx !== -1) entries.value[idx] = updated
  }

  return {
    currentLog, entries, loading, selectedDate,
    mealTypes, entriesByMeal, totals, recentFoods, mealTotals,
    loadDay, addEntry, removeEntry, updateEntry,
  }
})