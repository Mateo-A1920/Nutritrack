<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoutineStore } from '@/stores/routineStore'
import type { Routine, RoutineExercise, TrainingCategory, ExerciseLog, WorkoutSession } from '@/types'

const store = useRoutineStore()

const showForm        = ref(false)
const showSession     = ref(false)
const showDetail      = ref(false)
const editTarget      = ref<Routine | null>(null)
const selectedSession = ref<WorkoutSession | null>(null)
const activeCategory  = ref<TrainingCategory | 'todos'>('todos')

const days       = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']
const categories: { value: TrainingCategory | 'todos'; label: string }[] = [
  { value: 'todos',   label: 'Todos' },
  { value: 'pesas',   label: 'Pesas' },
  { value: 'box',     label: 'Box' },
  { value: 'cardio',  label: 'Cardio' },
  { value: 'otro',    label: 'Otro' },
]

const categoryColors: Record<TrainingCategory, string> = {
  pesas:  'bg-zinc-900 text-white',
  box:    'bg-red-500 text-white',
  cardio: 'bg-blue-500 text-white',
  otro:   'bg-zinc-400 text-white',
}

const form = ref({
  name: '', description: '', day_of_week: '', category: 'otro' as TrainingCategory,
  exercises: [] as RoutineExercise[],
})

const sessionForm = ref({
  routine_id:       undefined as number | undefined,
  session_date:     new Date().toLocaleDateString('en-CA') as string,
  duration_minutes: undefined as number | undefined,
  notes:            '',
})

// Form para log de ejercicio en sesion
const newLog = ref({
  exercise_name:    '',
  set_number:       1,
  reps:             undefined as number | undefined,
  weight_kg:        undefined as number | undefined,
  duration_seconds: undefined as number | undefined,
  rounds:           undefined as number | undefined,
  notes:            '',
})

const showLogForm = ref(false)

onMounted(() => {
  store.fetchRoutines()
  store.fetchSessions()
})

const filteredRoutines = computed(() => {
  if (activeCategory.value === 'todos') return store.routines
  return store.routines.filter(r => r.category === activeCategory.value)
})

const routinesByCategory = computed(() => {
  const map: { [key: string]: Routine[] } = { pesas: [], box: [], cardio: [], otro: [] }
  for (const r of store.routines) {
    const key = (r.category as string) ?? 'otro'
    if (!map[key]) map[key] = []
    map[key].push(r)
  }
  return map
})

function openCreate() {
  editTarget.value = null
  form.value = { name: '', description: '', day_of_week: '', category: 'otro', exercises: [] }
  showForm.value = true
}

function openEdit(r: Routine) {
  editTarget.value = r
  form.value = {
    name:        r.name,
    description: r.description ?? '',
    day_of_week: r.day_of_week ?? '',
    category:    r.category ?? 'otro',
    exercises:   r.exercises.map(e => ({ ...e })),
  }
  showForm.value = true
}

function addExercise() {
  form.value.exercises.push({
    name:         '',
    sets:         undefined as unknown as number,
    reps:         undefined as unknown as number,
    weight_kg:    null,
    rest_seconds: undefined as unknown as number,
    notes:        '',
  })
}

function removeExercise(i: number) {
  form.value.exercises.splice(i, 1)
}

async function submitForm() {
  const payload = {
    name:        form.value.name,
    description: form.value.description || null,
    day_of_week: form.value.day_of_week || null,
    category:    form.value.category,
    exercises:   form.value.exercises,
  }
  if (editTarget.value) {
    await store.updateRoutine(editTarget.value.id, payload)
  } else {
    await store.createRoutine(payload as any)
  }
  showForm.value = false
}

async function submitSession() {
  if (!sessionForm.value.routine_id) return
  await store.logSession(sessionForm.value as any)
  showSession.value = false
  sessionForm.value = {
    routine_id: undefined, session_date: new Date().toLocaleDateString('en-CA') as string,
    duration_minutes: undefined, notes: '',
  }
}

async function openDetail(s: WorkoutSession) {
  selectedSession.value = s
  await store.fetchSessionDetail(s.id)
  showDetail.value = true
  showLogForm.value = false
  newLog.value = {
    exercise_name: '', set_number: (store.exerciseLogs.length + 1),
    reps: undefined, weight_kg: undefined,
    duration_seconds: undefined, rounds: undefined, notes: '',
  }
}

async function submitLog() {
  if (!selectedSession.value) return
  await store.addExerciseLog({
    workout_session_id: selectedSession.value.id,
    exercise_name:      newLog.value.exercise_name,
    set_number:         newLog.value.set_number,
    reps:               newLog.value.reps,
    weight_kg:          newLog.value.weight_kg,
    duration_seconds:   newLog.value.duration_seconds,
    rounds:             newLog.value.rounds,
    notes:              newLog.value.notes || null,
  })
  showLogForm.value = false
  newLog.value = {
    exercise_name: '', set_number: store.exerciseLogs.length + 1,
    reps: undefined, weight_kg: undefined,
    duration_seconds: undefined, rounds: undefined, notes: '',
  }
}

const sessionsByWeek = computed(() => {
  const now = new Date()
  const cutoff = new Date()
  cutoff.setDate(now.getDate() - 7)
  return store.sessions.filter(s => new Date(String(s.session_date).slice(0,10)) >= cutoff).length
})

const totalMinutesMonth = computed(() => {
  const now = new Date()
  const cutoff = new Date()
  cutoff.setMonth(now.getMonth() - 1)
  return store.sessions
    .filter(s => new Date(String(s.session_date).slice(0,10)) >= cutoff)
    .reduce((acc, s) => acc + (s.duration_minutes ?? 0), 0)
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <p class="text-xs text-zinc-400 uppercase tracking-wide font-mono mb-1">Entrenamiento</p>
        <h1 class="text-2xl font-semibold text-zinc-900">Rutinas</h1>
      </div>
      <div class="flex gap-2">
        <button @click="showSession = true" class="px-4 py-2 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-700 hover:border-zinc-900 transition-colors">
          Registrar sesion
        </button>
        <button @click="openCreate" class="px-4 py-2 rounded-lg bg-zinc-900 text-white text-sm hover:bg-zinc-700 transition-colors">
          Nueva rutina
        </button>
      </div>
    </div>

    <!-- Stats rapidas -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-2xl p-4">
        <p class="text-xs text-zinc-400 mb-1">Esta semana</p>
        <p class="text-2xl font-semibold font-mono text-zinc-900">{{ sessionsByWeek }}</p>
        <p class="text-xs text-zinc-400">sesiones</p>
      </div>
      <div class="bg-white rounded-2xl p-4">
        <p class="text-xs text-zinc-400 mb-1">Este mes</p>
        <p class="text-2xl font-semibold font-mono text-zinc-900">{{ totalMinutesMonth }}</p>
        <p class="text-xs text-zinc-400">minutos</p>
      </div>
      <div class="bg-white rounded-2xl p-4">
        <p class="text-xs text-zinc-400 mb-1">Rutinas</p>
        <p class="text-2xl font-semibold font-mono text-zinc-900">{{ store.routines.length }}</p>
        <p class="text-xs text-zinc-400">creadas</p>
      </div>
    </div>

    <!-- Filtro categorias -->
    <div class="flex gap-2 mb-4 flex-wrap">
      <button
        v-for="cat in categories"
        :key="cat.value"
        @click="activeCategory = cat.value"
        class="px-3 py-1.5 rounded-lg text-xs transition-colors font-medium"
        :class="activeCategory === cat.value ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-500 hover:bg-zinc-100 border border-zinc-200'"
      >
        {{ cat.label }}
      </button>
    </div>

    <!-- Lista rutinas -->
    <div v-if="store.loading" class="text-center py-16 text-sm text-zinc-400">Cargando...</div>

    <div v-else-if="filteredRoutines.length === 0" class="bg-white rounded-2xl p-12 text-center text-sm text-zinc-400">
      Sin rutinas en esta categoria
    </div>

    <div v-else class="space-y-3 mb-8">
      <div v-for="r in filteredRoutines" :key="r.id" class="bg-white rounded-2xl overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span
              class="text-[10px] font-semibold px-2 py-0.5 rounded-md uppercase tracking-wide"
              :class="categoryColors[r.category ?? 'otro']"
            >
              {{ r.category ?? 'otro' }}
            </span>
            <div>
              <h2 class="text-sm font-semibold text-zinc-900">{{ r.name }}</h2>
              <p v-if="r.day_of_week" class="text-xs text-zinc-400 capitalize">{{ r.day_of_week }}</p>
            </div>
          </div>
          <div class="flex gap-3">
            <button @click="openEdit(r)" class="text-xs text-zinc-400 hover:text-zinc-900 transition-colors">Editar</button>
            <button @click="store.deleteRoutine(r.id)" class="text-xs text-zinc-300 hover:text-red-400 transition-colors">Eliminar</button>
          </div>
        </div>
        <div v-if="r.exercises.length" class="border-t border-zinc-50 divide-y divide-zinc-50">
          <div v-for="ex in r.exercises" :key="ex.id" class="px-5 py-2.5 flex items-center justify-between">
            <span class="text-sm text-zinc-700">{{ ex.name }}</span>
            <span class="text-xs text-zinc-400 font-mono">
              {{ ex.sets }}x{{ ex.reps }}<span v-if="ex.weight_kg"> · {{ ex.weight_kg }}kg</span>
              <span v-if="ex.rest_seconds"> · {{ ex.rest_seconds }}s</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sesiones recientes -->
    <div>
      <h2 class="text-sm font-semibold text-zinc-700 mb-3">Historial de sesiones</h2>
      <div v-if="store.sessions.length === 0" class="bg-white rounded-2xl p-8 text-center text-sm text-zinc-400">
        Sin sesiones registradas
      </div>
      <div v-else class="bg-white rounded-2xl overflow-hidden">
        <div
          v-for="s in store.sessions.slice(0, 20)"
          :key="s.id"
          class="session-row px-5 py-3 flex items-center justify-between border-b border-zinc-50 last:border-0 cursor-pointer hover:bg-zinc-50 transition-colors"
          @click="openDetail(s)"
        >
          <div class="flex items-center gap-3">
            <span
              v-if="s.routine?.category"
              class="text-[10px] font-semibold px-2 py-0.5 rounded-md uppercase"
              :class="categoryColors[s.routine.category]"
            >
              {{ s.routine.category }}
            </span>
            <div>
              <p class="text-sm text-zinc-800 font-medium">{{ s.routine?.name ?? 'Sesion' }}</p>
              <p class="text-xs text-zinc-400">{{ String(s.session_date).slice(0, 10) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <span v-if="s.duration_minutes" class="text-xs text-zinc-500 font-mono">{{ s.duration_minutes }} min</span>
            <button
              @click.stop="store.deleteSession(s.id)"
              class="session-delete text-zinc-300 hover:text-red-400 text-base leading-none transition-colors"
            >&times;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal rutina -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 px-4 py-8 overflow-y-auto">
      <div class="bg-white rounded-2xl w-full max-w-xl p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-base font-semibold text-zinc-900">{{ editTarget ? 'Editar rutina' : 'Nueva rutina' }}</h2>
          <button @click="showForm = false" class="text-zinc-400 hover:text-zinc-700 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Nombre</label>
              <input v-model="form.name" type="text" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
            </div>
            <div>
              <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Tipo</label>
              <select v-model="form.category" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 bg-white transition-colors">
                <option value="pesas">Pesas</option>
                <option value="box">Box</option>
                <option value="cardio">Cardio</option>
                <option value="otro">Otro</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Dia</label>
              <select v-model="form.day_of_week" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 bg-white transition-colors">
                <option value="">Sin dia fijo</option>
                <option v-for="d in days" :key="d" :value="d" class="capitalize">{{ d }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Descripcion</label>
              <input v-model="form.description" type="text" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs text-zinc-500 uppercase tracking-wide">Ejercicios</label>
              <button type="button" @click="addExercise" class="text-xs text-zinc-500 hover:text-zinc-900 transition-colors">+ Agregar</button>
            </div>
            <div class="space-y-3">
              <div v-for="(ex, i) in form.exercises" :key="i" class="border border-zinc-100 rounded-xl p-3">
                <div class="grid grid-cols-2 gap-2 mb-2">
                  <div class="col-span-2">
                    <input v-model="ex.name" placeholder="Nombre del ejercicio" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
                  </div>
                  <input v-model.number="ex.sets" type="number" min="1" placeholder="Series" class="border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
                  <input v-model.number="ex.reps" type="number" min="1" placeholder="Reps" class="border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
                  <input v-model.number="ex.weight_kg" type="number" min="0" step="0.5" placeholder="Peso (kg)" class="border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
                  <input v-model.number="ex.rest_seconds" type="number" min="0" placeholder="Descanso (seg)" class="border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
                </div>
                <button type="button" @click="removeExercise(i)" class="text-xs text-zinc-300 hover:text-red-400 transition-colors">Quitar</button>
              </div>
            </div>
          </div>

          <div class="flex gap-2 pt-2">
            <button type="button" @click="showForm = false" class="flex-1 border border-zinc-200 text-zinc-600 rounded-lg px-4 py-2 text-sm hover:bg-zinc-50 transition-colors">Cancelar</button>
            <button type="submit" class="flex-1 bg-zinc-900 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-zinc-700 transition-colors">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal sesion -->
    <div v-if="showSession" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white rounded-2xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-base font-semibold text-zinc-900">Registrar sesion</h2>
          <button @click="showSession = false" class="text-zinc-400 hover:text-zinc-700 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitSession" class="space-y-4">
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Rutina</label>
            <select v-model="sessionForm.routine_id" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 bg-white transition-colors">
              <option :value="undefined" disabled>Selecciona una rutina</option>
              <option v-for="r in store.routines" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Fecha</label>
            <input v-model="sessionForm.session_date" type="date" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
          </div>
          <div>
            <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wide">Duracion (min)</label>
            <input v-model.number="sessionForm.duration_minutes" type="number" min="1" placeholder="Ej. 60" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showSession = false" class="flex-1 border border-zinc-200 text-zinc-600 rounded-lg px-4 py-2 text-sm hover:bg-zinc-50 transition-colors">Cancelar</button>
            <button type="submit" class="flex-1 bg-zinc-900 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-zinc-700 transition-colors">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal detalle sesion -->
    <div v-if="showDetail" class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 px-4 py-8 overflow-y-auto">
      <div class="bg-white rounded-2xl w-full max-w-xl p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-base font-semibold text-zinc-900">{{ selectedSession?.routine?.name }}</h2>
            <p class="text-xs text-zinc-400">{{ String(selectedSession?.session_date).slice(0,10) }}
              <span v-if="selectedSession?.duration_minutes"> · {{ selectedSession.duration_minutes }} min</span>
            </p>
          </div>
          <button @click="showDetail = false" class="text-zinc-400 hover:text-zinc-700 text-xl">&times;</button>
        </div>

        <!-- Logs existentes -->
        <div v-if="store.exerciseLogs.length" class="mb-4">
          <p class="text-xs text-zinc-400 uppercase tracking-wide mb-2">Ejercicios registrados</p>
          <div class="space-y-1">
            <div
              v-for="log in store.exerciseLogs"
              :key="log.id"
              class="flex items-center justify-between px-4 py-2.5 bg-zinc-50 rounded-xl"
            >
              <div>
                <span class="text-sm text-zinc-800 font-medium">{{ log.exercise_name }}</span>
                <span class="text-xs text-zinc-400 ml-2">Serie {{ log.set_number }}</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-xs font-mono text-zinc-600">
                  <span v-if="log.reps">{{ log.reps }} reps</span>
                  <span v-if="log.weight_kg"> · {{ log.weight_kg }}kg</span>
                  <span v-if="log.rounds"> · {{ log.rounds }} rounds</span>
                  <span v-if="log.duration_seconds"> · {{ log.duration_seconds }}s</span>
                </span>
                <button @click="store.deleteExerciseLog(log.id)" class="text-zinc-300 hover:text-red-400 text-base leading-none transition-colors">&times;</button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="mb-4 text-xs text-zinc-400 text-center py-4">Sin ejercicios registrados en esta sesion</div>

        <!-- Form nuevo log -->
        <div v-if="showLogForm" class="border border-zinc-100 rounded-xl p-4 mb-4">
          <p class="text-xs text-zinc-500 uppercase tracking-wide mb-3">Agregar ejercicio</p>
          <div class="space-y-3">
            <input v-model="newLog.exercise_name" type="text" placeholder="Nombre del ejercicio" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
            <div class="grid grid-cols-3 gap-2">
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Serie</label>
                <input v-model.number="newLog.set_number" type="number" min="1" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Reps</label>
                <input v-model.number="newLog.reps" type="number" min="0" placeholder="--" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Peso (kg)</label>
                <input v-model.number="newLog.weight_kg" type="number" min="0" step="0.5" placeholder="--" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Rounds</label>
                <input v-model.number="newLog.rounds" type="number" min="0" placeholder="--" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Seg</label>
                <input v-model.number="newLog.duration_seconds" type="number" min="0" placeholder="--" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-zinc-400 mb-1">Notas</label>
                <input v-model="newLog.notes" type="text" placeholder="--" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors" />
              </div>
            </div>
            <div class="flex gap-2">
              <button type="button" @click="showLogForm = false" class="flex-1 border border-zinc-200 text-zinc-600 rounded-lg px-3 py-2 text-sm hover:bg-zinc-50 transition-colors">Cancelar</button>
              <button type="button" @click="submitLog" class="flex-1 bg-zinc-900 text-white rounded-lg px-3 py-2 text-sm hover:bg-zinc-700 transition-colors">Guardar</button>
            </div>
          </div>
        </div>

        <button
          v-if="!showLogForm"
          @click="showLogForm = true; newLog.set_number = store.exerciseLogs.length + 1"
          class="w-full border border-dashed border-zinc-300 text-zinc-500 rounded-xl py-2.5 text-sm hover:border-zinc-900 hover:text-zinc-900 transition-colors"
        >
          + Agregar ejercicio
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.session-delete { opacity: 0; transition: opacity 0.15s; }
.session-row:hover .session-delete { opacity: 1; }
</style>