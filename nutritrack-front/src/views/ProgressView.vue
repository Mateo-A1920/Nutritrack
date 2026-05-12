<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useProgressStore } from '@/stores/progressStore'
import { useRoutineStore } from '@/stores/routineStore'
import type { BodyMeasurement } from '@/types'

const progress = useProgressStore()
const routine  = useRoutineStore()

const showMeasurementForm = ref(false)
const showGoalsForm       = ref(false)
const statPeriod          = ref<'semana' | 'mes' | 'año'>('semana')

const measureForm = ref({
  measured_at:  new Date().toLocaleDateString('en-CA'),
  weight_kg:    undefined as number | undefined,
  height_cm:    undefined as number | undefined,
  waist_cm:     undefined as number | undefined,
  hip_cm:       undefined as number | undefined,
  chest_cm:     undefined as number | undefined,
  arm_cm:       undefined as number | undefined,
  leg_cm:       undefined as number | undefined,
  body_fat_pct: undefined as number | undefined,
})

const goalsForm = ref({
  calories_goal:      2000,
  water_glasses_goal: 8,
  target_weight_kg:   undefined as number | undefined,
})

onMounted(async () => {
  await Promise.all([
    progress.fetchMeasurements(),
    progress.fetchGoals(),
    routine.fetchSessions(),
  ])
  goalsForm.value = {
    calories_goal:      progress.goals.calories_goal,
    water_glasses_goal: progress.goals.water_glasses_goal,
    target_weight_kg:   progress.goals.target_weight_kg ?? undefined,
  }
})

async function submitMeasurement() {
  await progress.addMeasurement(measureForm.value as Omit<BodyMeasurement, 'id' | 'user_id'>)
  showMeasurementForm.value = false
  measureForm.value = {
    measured_at: new Date().toLocaleDateString('en-CA'),
    weight_kg: undefined, height_cm: undefined, waist_cm: undefined,
    hip_cm: undefined, chest_cm: undefined, arm_cm: undefined,
    leg_cm: undefined, body_fat_pct: undefined,
  }
}

async function submitGoals() {
  await progress.updateGoals(goalsForm.value)
  showGoalsForm.value = false
}

const sessionStats = computed(() => {
  const now = new Date()
  const cutoff = new Date()
  if (statPeriod.value === 'semana') cutoff.setDate(now.getDate() - 7)
  else if (statPeriod.value === 'mes') cutoff.setMonth(now.getMonth() - 1)
  else cutoff.setFullYear(now.getFullYear() - 1)

  const filtered = routine.sessions.filter(s =>
    new Date(String(s.session_date).slice(0, 10)) >= cutoff
  )

  const byCategory: Record<string, number> = {}
  for (const s of filtered) {
    const cat = s.routine?.category ?? 'otro'
    byCategory[cat] = (byCategory[cat] ?? 0) + 1
  }

  return {
    total:        filtered.length,
    totalMinutes: filtered.reduce((acc, s) => acc + (s.duration_minutes ?? 0), 0),
    avgMinutes:   filtered.length
      ? Math.round(filtered.reduce((acc, s) => acc + (s.duration_minutes ?? 0), 0) / filtered.length)
      : 0,
    byCategory,
  }
})

const recommendations = computed(() => {
  const tips: { type: 'good' | 'warn' | 'info'; text: string }[] = []
  const b = Number(progress.bmi)

  const sessions7 = routine.sessions.filter(s => {
    const cutoff = new Date()
    cutoff.setDate(cutoff.getDate() - 7)
    return new Date(String(s.session_date).slice(0, 10)) >= cutoff
  }).length

  if (b > 0 && b < 18.5) tips.push({ type: 'warn', text: 'Tu IMC indica bajo peso. Considera aumentar tu ingesta calorica.' })
  if (b >= 18.5 && b < 25) tips.push({ type: 'good', text: 'Tu IMC esta en rango normal. Sigue manteniendo tus habitos.' })
  if (b >= 25 && b < 30) tips.push({ type: 'warn', text: 'Tu IMC indica sobrepeso. Considera reducir calorias y aumentar actividad fisica.' })
  if (b >= 30) tips.push({ type: 'warn', text: 'Tu IMC indica obesidad. Se recomienda consultar con un profesional de salud.' })

  if (sessions7 >= 4) tips.push({ type: 'good', text: `Entrenaste ${sessions7} veces esta semana. Excelente constancia.` })
  else if (sessions7 >= 2) tips.push({ type: 'info', text: `Entrenaste ${sessions7} veces esta semana. Intenta llegar a 4-5 sesiones.` })
  else tips.push({ type: 'warn', text: 'No registraste entrenamientos esta semana. Intenta al menos 2-3 sesiones.' })

  const m = progress.latestMeasurement
  if (m?.body_fat_pct && Number(m.body_fat_pct) > 25) tips.push({ type: 'warn', text: 'Tu porcentaje de grasa corporal es alto. El entrenamiento de fuerza ayuda a reducirlo.' })
  if (m?.body_fat_pct && Number(m.body_fat_pct) <= 20) tips.push({ type: 'good', text: 'Tu porcentaje de grasa corporal es saludable.' })

  if (progress.measurements.length >= 2) {
    const latest = Number(progress.measurements[0]?.weight_kg ?? 0)
    const prev   = Number(progress.measurements[1]?.weight_kg ?? 0)
    const diff   = latest - prev
    if (diff < -1) tips.push({ type: 'good', text: `Perdiste ${Math.abs(diff).toFixed(1)} kg desde tu ultima medicion.` })
    if (diff > 1)  tips.push({ type: 'info', text: `Ganaste ${diff.toFixed(1)} kg desde tu ultima medicion.` })
  }

  if (tips.length === 0) tips.push({ type: 'info', text: 'Agrega mediciones corporales para recibir recomendaciones personalizadas.' })

  return tips
})

const weightHistory = computed(() =>
  progress.measurements.filter(m => m.weight_kg).slice(0, 8).reverse()
)

const bmiLabel = computed(() => {
  const b = Number(progress.bmi)
  if (!b) return ''
  if (b < 18.5) return 'Bajo peso'
  if (b < 25)   return 'Normal'
  if (b < 30)   return 'Sobrepeso'
  return 'Obesidad'
})

const measureFields = [
  { key: 'weight_kg'    as const, label: 'Peso (kg)' },
  { key: 'height_cm'   as const, label: 'Altura (cm)' },
  { key: 'waist_cm'    as const, label: 'Cintura (cm)' },
  { key: 'hip_cm'      as const, label: 'Cadera (cm)' },
  { key: 'chest_cm'    as const, label: 'Pecho (cm)' },
  { key: 'arm_cm'      as const, label: 'Brazo (cm)' },
  { key: 'leg_cm'      as const, label: 'Pierna (cm)' },
  { key: 'body_fat_pct'as const, label: 'Grasa (%)' },
]

const categoryColors: Record<string, string> = {
  pesas:  'bg-zinc-900 text-white',
  box:    'bg-red-500 text-white',
  cardio: 'bg-blue-500 text-white',
  otro:   'bg-zinc-400 text-white',
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <p class="text-xs text-zinc-400 uppercase tracking-wide font-mono mb-1">Seguimiento</p>
        <h1 class="text-2xl font-semibold text-zinc-900">Progreso</h1>
      </div>
      <div class="flex gap-2">
        <button @click="showGoalsForm = true" class="px-4 py-2 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-700 hover:border-zinc-900 transition-colors">
          Metas
        </button>
        <button @click="showMeasurementForm = true" class="px-4 py-2 rounded-lg bg-zinc-900 text-white text-sm hover:bg-zinc-700 transition-colors">
          Nueva medicion
        </button>
      </div>
    </div>

    <!-- Metricas principales -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl p-5">
        <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">IMC</p>
        <p class="text-3xl font-semibold font-mono text-zinc-900">{{ progress.bmi ?? '--' }}</p>
        <p class="text-xs text-zinc-500 mt-1">{{ bmiLabel }}</p>
      </div>
      <div class="bg-white rounded-2xl p-5">
        <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Peso actual</p>
        <p class="text-3xl font-semibold font-mono text-zinc-900">{{ progress.latestMeasurement?.weight_kg ?? '--' }}</p>
        <p class="text-xs text-zinc-500 mt-1">kg</p>
      </div>
      <div class="bg-white rounded-2xl p-5">
        <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Meta peso</p>
        <p class="text-3xl font-semibold font-mono text-zinc-900">{{ progress.goals.target_weight_kg ?? '--' }}</p>
        <p class="text-xs text-zinc-500 mt-1">kg</p>
      </div>
      <div class="bg-white rounded-2xl p-5">
        <p class="text-xs text-zinc-400 uppercase tracking-wide mb-1">Grasa corporal</p>
        <p class="text-3xl font-semibold font-mono text-zinc-900">{{ progress.latestMeasurement?.body_fat_pct ?? '--' }}</p>
        <p class="text-xs text-zinc-500 mt-1">%</p>
      </div>
    </div>

    <!-- Estadisticas de entrenamiento -->
    <div class="bg-white rounded-2xl p-5 mb-6">
      <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Estadisticas de entrenamiento</p>
        <div class="flex gap-1">
          <button
            v-for="p in (['semana', 'mes', 'año'] as const)"
            :key="p"
            @click="statPeriod = p"
            class="px-3 py-1 rounded-lg text-xs transition-colors"
            :class="statPeriod === p ? 'bg-zinc-900 text-white' : 'text-zinc-500 hover:bg-zinc-100'"
          >
            {{ p.charAt(0).toUpperCase() + p.slice(1) }}
          </button>
        </div>
      </div>
      <div class="grid grid-cols-3 gap-4 mb-4">
        <div>
          <p class="text-2xl font-semibold font-mono text-zinc-900">{{ sessionStats.total }}</p>
          <p class="text-xs text-zinc-400 mt-1">sesiones</p>
        </div>
        <div>
          <p class="text-2xl font-semibold font-mono text-zinc-900">{{ sessionStats.totalMinutes }}</p>
          <p class="text-xs text-zinc-400 mt-1">minutos totales</p>
        </div>
        <div>
          <p class="text-2xl font-semibold font-mono text-zinc-900">{{ sessionStats.avgMinutes }}</p>
          <p class="text-xs text-zinc-400 mt-1">min promedio</p>
        </div>
      </div>
      <!-- Por categoria -->
      <div v-if="Object.keys(sessionStats.byCategory).length" class="flex gap-2 flex-wrap">
        <div
          v-for="(count, cat) in sessionStats.byCategory"
          :key="cat"
          class="flex items-center gap-2 px-3 py-1.5 rounded-lg"
          :class="categoryColors[cat] ?? 'bg-zinc-100 text-zinc-600'"
        >
          <span class="text-xs font-medium capitalize">{{ cat }}</span>
          <span class="text-xs font-mono font-semibold">{{ count }}</span>
        </div>
      </div>
    </div>

    <!-- Recomendaciones -->
    <div class="bg-white rounded-2xl p-5 mb-6">
      <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-4">Recomendaciones</p>
      <div class="space-y-2">
        <div
          v-for="(tip, i) in recommendations"
          :key="i"
          class="flex items-start gap-3 p-3 rounded-xl"
          :class="{
            'bg-green-50': tip.type === 'good',
            'bg-amber-50': tip.type === 'warn',
            'bg-zinc-50':  tip.type === 'info',
          }"
        >
          <div
            class="w-1.5 h-1.5 rounded-full mt-2 shrink-0"
            :class="{
              'bg-green-400': tip.type === 'good',
              'bg-amber-400': tip.type === 'warn',
              'bg-zinc-400':  tip.type === 'info',
            }"
          ></div>
          <p class="text-sm text-zinc-700">{{ tip.text }}</p>
        </div>
      </div>
    </div>

    <!-- Historial de peso -->
    <div v-if="weightHistory.length > 1" class="bg-white rounded-2xl p-5 mb-6">
      <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-4">Historial de peso</p>
      <div class="flex items-end gap-2 h-24">
        <div v-for="m in weightHistory" :key="m.id" class="flex-1 flex flex-col items-center gap-1">
          <span class="text-[10px] text-zinc-400 font-mono">{{ m.weight_kg }}</span>
          <div
            class="w-full bg-zinc-900 rounded-t-sm"
            :style="{
              height: ((Number(m.weight_kg) - Math.min(...weightHistory.map(x => Number(x.weight_kg)))) /
                ((Math.max(...weightHistory.map(x => Number(x.weight_kg))) - Math.min(...weightHistory.map(x => Number(x.weight_kg)))) || 1)) * 64 + 8 + 'px'
            }"
          ></div>
          <span class="text-[10px] text-zinc-400">{{ String(m.measured_at).slice(5, 10) }}</span>
        </div>
      </div>
    </div>

    <!-- Tabla mediciones -->
    <div class="bg-white rounded-2xl overflow-hidden">
      <div class="px-5 py-4 border-b border-zinc-100">
        <h2 class="text-sm font-semibold text-zinc-900">Historial de mediciones</h2>
      </div>
      <div v-if="progress.loading" class="p-8 text-center text-sm text-zinc-400">Cargando...</div>
      <div v-else-if="progress.measurements.length === 0" class="p-8 text-center text-sm text-zinc-400">Sin mediciones registradas</div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs">
          <thead>
            <tr class="border-b border-zinc-100">
              <th class="text-left px-5 py-2 text-zinc-400 font-medium">Fecha</th>
              <th v-for="f in measureFields" :key="f.key" class="text-right px-3 py-2 text-zinc-400 font-medium whitespace-nowrap">{{ f.label }}</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in progress.measurements" :key="m.id" class="border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
              <td class="px-5 py-3 text-zinc-700 font-medium">{{ String(m.measured_at).slice(0, 10) }}</td>
              <td v-for="f in measureFields" :key="f.key" class="text-right px-3 py-3 text-zinc-600 font-mono">{{ m[f.key] ?? '--' }}</td>
              <td class="px-3 py-3">
                <button @click="progress.deleteMeasurement(m.id)" class="text-zinc-300 hover:text-red-400 transition-colors text-base leading-none">&times;</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal medicion -->
    <div v-if="showMeasurementForm" class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 px-4 py-8 overflow-y-auto">
      <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-base font-semibold text-zinc-900">Nueva medicion</h2>
          <button @click="showMeasurementForm = false" class="text-zinc-400 hover:text-zinc-700 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitMeasurement" class="space-y-3">
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Fecha</label>
            <input v-model="measureForm.measured_at" type="date" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div v-for="f in measureFields" :key="f.key">
              <label class="block text-xs text-zinc-400 mb-1">{{ f.label }}</label>
              <input v-model.number="measureForm[f.key]" type="number" min="0" step="0.1" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
            </div>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showMeasurementForm = false" class="flex-1 border border-zinc-200 text-zinc-600 rounded-lg px-4 py-2 text-sm hover:bg-zinc-50 transition-colors">Cancelar</button>
            <button type="submit" class="flex-1 bg-zinc-900 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-zinc-700 transition-colors">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal metas -->
    <div v-if="showGoalsForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white rounded-2xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-base font-semibold text-zinc-900">Mis metas</h2>
          <button @click="showGoalsForm = false" class="text-zinc-400 hover:text-zinc-700 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitGoals" class="space-y-4">
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Meta de calorias diarias</label>
            <input v-model.number="goalsForm.calories_goal" type="number" min="500" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
          </div>
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Meta de vasos de agua diarios</label>
            <input v-model.number="goalsForm.water_glasses_goal" type="number" min="1" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
          </div>
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Peso objetivo (kg)</label>
            <input v-model.number="goalsForm.target_weight_kg" type="number" min="0" step="0.1" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showGoalsForm = false" class="flex-1 border border-zinc-200 text-zinc-600 rounded-lg px-4 py-2 text-sm hover:bg-zinc-50 transition-colors">Cancelar</button>
            <button type="submit" class="flex-1 bg-zinc-900 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-zinc-700 transition-colors">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>