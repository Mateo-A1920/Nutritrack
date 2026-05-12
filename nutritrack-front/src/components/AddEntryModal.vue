<script setup lang="ts">
import { ref, watch } from 'vue'
import type { MealType, FoodEntry } from '@/types'
import { useNutriStore } from '@/stores/nutriStore'

const props = defineProps<{
  meal: MealType
  editEntry?: FoodEntry | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: {
    meal_type: MealType; name: string
    calories: number; carbs: number; fats: number
    protein: number; sodium: number; sugar: number
  }): void
}>()

const nutri = useNutriStore()

const name     = ref(props.editEntry?.name ?? '')
const calories = ref<number>(props.editEntry?.calories ?? 0)
const carbs    = ref<number>(props.editEntry?.carbs ?? 0)
const fats     = ref<number>(props.editEntry?.fats ?? 0)
const protein  = ref<number>(props.editEntry?.protein ?? 0)
const sodium   = ref<number>(props.editEntry?.sodium ?? 0)
const sugar    = ref<number>(props.editEntry?.sugar ?? 0)

const showSuggestions = ref(false)
const filtered = ref<FoodEntry[]>([])

watch(name, (val) => {
  if (val.length < 2) { filtered.value = []; showSuggestions.value = false; return }
  filtered.value = nutri.recentFoods.filter(f =>
    f.name.toLowerCase().includes(val.toLowerCase())
  )
  showSuggestions.value = filtered.value.length > 0
})

function selectFood(f: FoodEntry) {
  name.value     = f.name
  calories.value = Number(f.calories)
  carbs.value    = Number(f.carbs)
  fats.value     = Number(f.fats)
  protein.value  = Number(f.protein)
  sodium.value   = Number(f.sodium)
  sugar.value    = Number(f.sugar)
  showSuggestions.value = false
}

function submit() {
  emit('submit', {
    meal_type: props.meal,
    name:      name.value,
    calories:  calories.value,
    carbs:     carbs.value,
    fats:      fats.value,
    protein:   protein.value,
    sodium:    sodium.value,
    sugar:     sugar.value,
  })
}

const fields = [
  { label: 'Calorias (kcal)', model: calories },
  { label: 'Carbohidratos (g)', model: carbs },
  { label: 'Grasas (g)', model: fats },
  { label: 'Proteinas (g)', model: protein },
  { label: 'Sodio (mg)', model: sodium },
  { label: 'Azucar (g)', model: sugar },
]
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-base font-semibold text-zinc-900 capitalize">
          {{ editEntry ? 'Editar entrada' : 'Agregar a ' + meal }}
        </h2>
        <button @click="emit('close')" class="text-zinc-400 hover:text-zinc-700 text-xl leading-none">&times;</button>
      </div>

      <form @submit.prevent="submit" class="space-y-3">
        <div class="relative">
          <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wide">Alimento</label>
          <input
            v-model="name"
            type="text"
            required
            placeholder="Ej. Pechuga de pollo, 150g"
            autocomplete="off"
            class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors"
          />
          <div v-if="showSuggestions" class="absolute z-10 w-full bg-white border border-zinc-200 rounded-lg mt-1 shadow-lg overflow-hidden">
            <button
              v-for="f in filtered"
              :key="f.id"
              type="button"
              @click="selectFood(f)"
              class="w-full text-left px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 transition-colors border-b border-zinc-50 last:border-0"
            >
              <span class="font-medium">{{ f.name }}</span>
              <span class="text-zinc-400 ml-2 text-xs">{{ f.calories }} kcal</span>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div v-for="field in fields" :key="field.label">
            <label class="block text-xs text-zinc-400 mb-1">{{ field.label }}</label>
            <input
              v-model="field.model.value"
              type="number"
              min="0"
              step="0.1"
              required
              class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-zinc-900 transition-colors"
            />
          </div>
        </div>

        <div class="flex gap-2 pt-2">
          <button type="button" @click="emit('close')" class="flex-1 border border-zinc-200 text-zinc-600 rounded-lg px-4 py-2 text-sm hover:bg-zinc-50 transition-colors">
            Cancelar
          </button>
          <button type="submit" class="flex-1 bg-zinc-900 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-zinc-700 transition-colors">
            {{ editEntry ? 'Guardar cambios' : 'Agregar' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>