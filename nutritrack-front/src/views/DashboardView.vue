<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useNutriStore } from '@/stores/nutriStore'
import { useProgressStore } from '@/stores/progressStore'
import MealSection from '@/components/MealSection.vue'
import AddEntryModal from '@/components/AddEntryModal.vue'
import type { MealType, FoodEntry } from '@/types'

const nutri    = useNutriStore()
const progress = useProgressStore()

const activeMeal   = ref<MealType | null>(null)
const showCalendar = ref(false)
const editEntry    = ref<FoodEntry | null>(null)
const editMeal     = ref<MealType | null>(null)
const calViewDate  = ref(new Date(nutri.selectedDate + 'T12:00:00'))
const todayStr     = new Date().toLocaleDateString('en-CA')

onMounted(async () => {
  await Promise.all([
    nutri.loadDay(nutri.selectedDate),
    progress.fetchWater(nutri.selectedDate),
    progress.fetchGoals(),
  ])
})

async function changeDate(delta: number) {
  const d = new Date(nutri.selectedDate + 'T12:00:00')
  d.setDate(d.getDate() + delta)
  const newDate = d.toLocaleDateString('en-CA')
  await Promise.all([
    nutri.loadDay(newDate),
    progress.fetchWater(newDate),
  ])
}

function calMonth(delta: number) {
  const d = new Date(calViewDate.value)
  d.setMonth(d.getMonth() + delta)
  calViewDate.value = d
}

function selectDate(date: string) {
  nutri.loadDay(date)
  progress.fetchWater(date)
  showCalendar.value = false
}

function formatDate(dateStr: string) {
  return new Date(dateStr + 'T12:00:00').toLocaleDateString('es-MX', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
  })
}

async function handleAddEntry(payload: Parameters<typeof nutri.addEntry>[0]) {
  await nutri.addEntry(payload)
  activeMeal.value = null
}

function openEdit(entry: FoodEntry) {
  editEntry.value = entry
  editMeal.value  = entry.meal_type
}

async function handleEditEntry(payload: Parameters<typeof nutri.addEntry>[0]) {
  if (editEntry.value) {
    await nutri.updateEntry(editEntry.value.id, payload)
  }
  editEntry.value = null
  editMeal.value  = null
}

async function addWater() {
  await progress.setWater(nutri.selectedDate, progress.waterLog.glasses + 1)
}

async function removeWater() {
  if (progress.waterLog.glasses <= 0) return
  await progress.setWater(nutri.selectedDate, progress.waterLog.glasses - 1)
}

const caloriesPercent = computed(() => {
  const pct = (nutri.totals.calories / progress.goals.calories_goal) * 100
  return Math.min(pct, 100).toFixed(0)
})

const waterPercent = computed(() => {
  const pct = (progress.waterLog.glasses / progress.goals.water_glasses_goal) * 100
  return Math.min(pct, 100).toFixed(0)
})

const calendarDays = computed(() => {
  const today = calViewDate.value
  const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
  const dayOfWeek = (startOfMonth.getDay() + 6) % 7
  const start = new Date(startOfMonth)
  start.setDate(start.getDate() - dayOfWeek)

  const days: string[] = []
  for (let i = 0; i < 35; i++) {
    const d = new Date(start)
    d.setDate(start.getDate() + i)
    days.push(d.toLocaleDateString('en-CA'))
  }
  return days
})

const statCols = [
  { label: 'Calorias',  key: 'calories' as const, unit: 'kcal' },
  { label: 'Carbs',     key: 'carbs'    as const, unit: 'g' },
  { label: 'Grasas',    key: 'fats'     as const, unit: 'g' },
  { label: 'Proteinas', key: 'protein'  as const, unit: 'g' },
  { label: 'Sodio',     key: 'sodium'   as const, unit: 'mg' },
  { label: 'Azucar',    key: 'sugar'    as const, unit: 'g' },
]
</script>

<template>
  <div>
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <p class="text-xs text-zinc-400 uppercase tracking-wide font-mono mb-1">Registro diario</p>
        <h1 class="text-2xl font-semibold text-zinc-900 capitalize">{{ formatDate(nutri.selectedDate) }}</h1>
      </div>

      <!-- NAV + CALENDARIO POPOVER -->
      <div class="flex items-center gap-2">
        <button
          @click="changeDate(-1)"
          class="w-8 h-8 rounded-lg border border-zinc-200 bg-white flex items-center justify-center text-zinc-600 hover:border-zinc-900 transition-colors text-sm"
        >&lt;</button>

        <!-- Wrapper relativo para el popover -->
        <div class="relative">
          <button
            @click="showCalendar = !showCalendar"
            class="px-3 h-8 rounded-lg border bg-white text-xs transition-colors"
            :class="showCalendar
              ? 'border-zinc-900 text-zinc-900'
              : 'border-zinc-200 text-zinc-600 hover:border-zinc-900'"
          >
            Calendario
          </button>

          <!-- POPOVER CALENDARIO -->
          <div
            v-if="showCalendar"
            class="absolute right-0 top-10 z-50 w-72 bg-white border border-zinc-200 rounded-2xl shadow-lg p-4"
          >
            <!-- Header mes -->
            <div class="flex items-center justify-between mb-4">
              <button
                @click="calMonth(-1)"
                class="w-6 h-6 rounded-lg border border-zinc-200 bg-zinc-50 flex items-center justify-center text-zinc-400 hover:border-zinc-900 hover:text-zinc-900 transition-colors text-sm"
              >‹</button>
              <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-widest">
                {{ calViewDate.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' }) }}
              </span>
              <button
                @click="calMonth(1)"
                class="w-6 h-6 rounded-lg border border-zinc-200 bg-zinc-50 flex items-center justify-center text-zinc-400 hover:border-zinc-900 hover:text-zinc-900 transition-colors text-sm"
              >›</button>
            </div>

            <!-- Días de semana -->
            <div class="grid grid-cols-7 mb-1">
              <div
                v-for="d in ['L','M','X','J','V','S','D']"
                :key="d"
                class="text-center text-[10px] text-zinc-300 font-medium pb-1"
              >{{ d }}</div>
            </div>

            <!-- Grid días -->
            <div class="grid grid-cols-7 gap-y-0.5">
              <button
                v-for="day in calendarDays"
                :key="day"
                @click="selectDate(day)"
                class="flex flex-col items-center gap-0.75 py-1 rounded-lg transition-colors"
                :class="day === nutri.selectedDate
                  ? 'bg-zinc-900'
                  : 'hover:bg-zinc-100'"
              >
                <span
                  class="text-[12px] leading-none tabular-nums"
                  :class="day === nutri.selectedDate
                    ? 'text-white font-medium'
                    : new Date(day + 'T12:00:00').getMonth() !== calViewDate.getMonth()
                      ? 'text-zinc-200'
                      : day === todayStr
                        ? 'text-blue-600 font-medium'
                        : 'text-zinc-700'"
                >{{ new Date(day + 'T12:00:00').getDate() }}</span>
              </button>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-100">
              <span class="text-[11px] text-zinc-400 capitalize">
                {{ new Date(nutri.selectedDate + 'T12:00:00').toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' }) }}
              </span>
              <button
                @click="selectDate(todayStr)"
                class="text-[11px] text-blue-600 hover:underline"
              >Hoy</button>
            </div>
          </div>
        </div>

        <button
          @click="changeDate(1)"
          class="w-8 h-8 rounded-lg border border-zinc-200 bg-white flex items-center justify-center text-zinc-600 hover:border-zinc-900 transition-colors text-sm"
        >&gt;</button>
      </div>
    </div>

    <!-- CARDS CALORÍAS Y AGUA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
      <div class="bg-white rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Calorias</p>
          <p class="text-xs text-zinc-400 font-mono">{{ nutri.totals.calories.toFixed(0) }} / {{ progress.goals.calories_goal }} kcal</p>
        </div>
        <div class="h-2 bg-zinc-100 rounded-full overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="Number(caloriesPercent) >= 100 ? 'bg-red-400' : 'bg-zinc-900'"
            :style="{ width: caloriesPercent + '%' }"
          ></div>
        </div>
        <p class="text-xs text-zinc-400 mt-2">{{ caloriesPercent }}% de la meta</p>
      </div>

      <div class="bg-white rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Agua</p>
          <p class="text-xs text-zinc-400 font-mono">{{ progress.waterLog.glasses }} / {{ progress.goals.water_glasses_goal }} vasos</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="flex gap-1 flex-1 flex-wrap">
            <div
              v-for="i in progress.goals.water_glasses_goal"
              :key="i"
              class="h-4 flex-1 min-w-2 rounded-sm transition-colors"
              :class="i <= progress.waterLog.glasses ? 'bg-blue-400' : 'bg-zinc-100'"
            ></div>
          </div>
          <div class="flex gap-1">
            <button @click="removeWater" class="w-6 h-6 rounded border border-zinc-200 text-zinc-500 hover:border-zinc-900 text-xs transition-colors">-</button>
            <button @click="addWater" class="w-6 h-6 rounded border border-zinc-200 text-zinc-500 hover:border-zinc-900 text-xs transition-colors">+</button>
          </div>
        </div>
        <p class="text-xs text-zinc-400 mt-2">{{ waterPercent }}% de la meta</p>
      </div>
    </div>

    <!-- STATS MACROS -->
    <div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-6">
      <div v-for="col in statCols" :key="col.key" class="bg-white rounded-xl px-4 py-3">
        <p class="text-xs text-zinc-400 mb-1">{{ col.label }}</p>
        <p class="text-base font-semibold font-mono text-zinc-900">
          {{ nutri.totals[col.key].toFixed(0) }}
          <span class="text-xs font-sans text-zinc-400">{{ col.unit }}</span>
        </p>
      </div>
    </div>

    <div v-if="nutri.loading" class="text-center py-16 text-sm text-zinc-400">Cargando...</div>

    <div v-else class="space-y-4">
      <MealSection
        v-for="meal in nutri.mealTypes"
        :key="meal"
        :meal="(meal as MealType)"
        :entries="nutri.entriesByMeal[(meal as MealType)]"
        :totals="nutri.mealTotals((meal as MealType))"
        @add="activeMeal = (meal as MealType)"
        @remove="nutri.removeEntry"
        @edit="openEdit"
      />
    </div>

    <!-- TOTALES -->
    <div class="mt-6 bg-zinc-900 rounded-2xl px-5 py-4">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <span class="text-sm font-semibold text-white">Totales del dia</span>
        <div class="flex flex-wrap gap-6">
          <div v-for="col in statCols" :key="col.key" class="text-right">
            <p class="text-xs text-zinc-400">{{ col.label }}</p>
            <p class="text-sm font-mono font-semibold text-white">{{ nutri.totals[col.key].toFixed(0) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <AddEntryModal
    v-if="activeMeal"
    :meal="activeMeal"
    @close="activeMeal = null"
    @submit="handleAddEntry"
  />

  <AddEntryModal
    v-if="editMeal"
    :meal="editMeal"
    :editEntry="editEntry"
    @close="editEntry = null; editMeal = null"
    @submit="handleEditEntry"
  />
</template>