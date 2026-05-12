export interface User {
  id: number
  name: string
  email: string
}

export interface DailyLog {
  id: number
  user_id: number
  log_date: string
}

export type MealType = 'desayuno' | 'almuerzo' | 'cena' | 'aperitivo'

export interface FoodEntry {
  id: number
  daily_log_id: number
  meal_type: MealType
  name: string
  calories: number
  carbs: number
  fats: number
  protein: number
  sodium: number
  sugar: number
}

export interface NutritionTotals {
  calories: number
  carbs: number
  fats: number
  protein: number
  sodium: number
  sugar: number
}

export interface DailyLogResponse {
  log: DailyLog | null
  entries: FoodEntry[]
}

export interface RoutineExercise {
  id?: number
  routine_id?: number
  name: string
  sets: number
  reps: number
  weight_kg?: number | null
  rest_seconds: number
  notes?: string | null
  order?: number
}

export type TrainingCategory = 'pesas' | 'box' | 'cardio' | 'otro'

export interface Routine {
  id: number
  user_id: number
  name: string
  description?: string | null
  day_of_week?: string | null
  category?: TrainingCategory | null
  exercises: RoutineExercise[]
}

export interface ExerciseLog {
  id: number
  workout_session_id: number
  exercise_name: string
  set_number: number
  reps?: number | null
  weight_kg?: number | null
  duration_seconds?: number | null
  rounds?: number | null
  notes?: string | null
}

export interface WorkoutSession {
  id: number
  user_id: number
  routine_id?: number | null
  routine?: Routine | null
  session_date: string
  duration_minutes?: number | null
  notes?: string | null
  exercise_logs?: ExerciseLog[]
}

export interface BodyMeasurement {
  id: number
  user_id: number
  measured_at: string
  weight_kg?: number | null
  height_cm?: number | null
  waist_cm?: number | null
  hip_cm?: number | null
  chest_cm?: number | null
  arm_cm?: number | null
  leg_cm?: number | null
  body_fat_pct?: number | null
}

export interface WaterLog {
  glasses: number
  log_date: string
}

export interface UserGoal {
  id?: number
  calories_goal: number
  water_glasses_goal: number
  target_weight_kg?: number | null
}