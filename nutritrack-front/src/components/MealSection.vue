<script setup lang="ts">
import type { FoodEntry, MealType, NutritionTotals } from '@/types'

defineProps<{
  meal: MealType
  entries: FoodEntry[]
  totals: NutritionTotals
}>()

const emit = defineEmits<{
  (e: 'add'): void
  (e: 'remove', id: number): void
  (e: 'edit', entry: FoodEntry): void
}>()

const mealLabel: Record<MealType, string> = {
  desayuno: 'Desayuno',
  almuerzo: 'Almuerzo',
  cena: 'Cena',
  aperitivo: 'Aperitivos',
}

const cols = ['Calorias', 'Carbs', 'Grasas', 'Proteinas', 'Sodio', 'Azucar']
</script>

<template>
  <div class="bg-white rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-zinc-900">{{ mealLabel[meal] }}</h2>
      <button @click="emit('add')" class="text-xs text-zinc-500 hover:text-zinc-900 transition-colors font-medium">
        + Anadir alimento
      </button>
    </div>

    <div v-if="entries.length" class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead>
          <tr class="border-b border-zinc-100">
            <th class="text-left px-5 py-2 text-zinc-400 font-medium">Alimento</th>
            <th v-for="col in cols" :key="col" class="text-right px-3 py-2 text-zinc-400 font-medium whitespace-nowrap">{{ col }}</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in entries" :key="entry.id" class="entry-row border-b border-zinc-50 hover:bg-zinc-50 transition-colors">
            <td class="px-5 py-3 text-zinc-700 font-medium">{{ entry.name }}</td>
            <td class="text-right px-3 py-3 text-zinc-600 font-mono">{{ entry.calories }}</td>
            <td class="text-right px-3 py-3 text-zinc-600 font-mono">{{ entry.carbs }}</td>
            <td class="text-right px-3 py-3 text-zinc-600 font-mono">{{ entry.fats }}</td>
            <td class="text-right px-3 py-3 text-zinc-600 font-mono">{{ entry.protein }}</td>
            <td class="text-right px-3 py-3 text-zinc-600 font-mono">{{ entry.sodium }}</td>
            <td class="text-right px-3 py-3 text-zinc-600 font-mono">{{ entry.sugar }}</td>
            <td class="px-3 py-3">
              <div class="entry-actions flex items-center gap-2">
                <button @click="emit('edit', entry)" class="text-zinc-400 hover:text-zinc-900 transition-colors text-xs">Editar</button>
                <button @click="emit('remove', entry.id)" class="text-zinc-300 hover:text-red-400 transition-colors text-base leading-none">&times;</button>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="bg-zinc-50">
            <td class="px-5 py-3 text-xs font-semibold text-zinc-500">Total</td>
            <td class="text-right px-3 py-3 text-xs font-semibold font-mono text-zinc-900">{{ totals.calories.toFixed(0) }}</td>
            <td class="text-right px-3 py-3 text-xs font-semibold font-mono text-zinc-900">{{ totals.carbs.toFixed(0) }}</td>
            <td class="text-right px-3 py-3 text-xs font-semibold font-mono text-zinc-900">{{ totals.fats.toFixed(0) }}</td>
            <td class="text-right px-3 py-3 text-xs font-semibold font-mono text-zinc-900">{{ totals.protein.toFixed(0) }}</td>
            <td class="text-right px-3 py-3 text-xs font-semibold font-mono text-zinc-900">{{ totals.sodium.toFixed(0) }}</td>
            <td class="text-right px-3 py-3 text-xs font-semibold font-mono text-zinc-900">{{ totals.sugar.toFixed(0) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div v-else class="px-5 py-6 text-xs text-zinc-400 text-center">
      Sin alimentos registrados
    </div>
  </div>
</template>

<style scoped>
.entry-actions {
  opacity: 0;
  transition: opacity 0.15s;
}
.entry-row:hover .entry-actions {
  opacity: 1;
}
</style>