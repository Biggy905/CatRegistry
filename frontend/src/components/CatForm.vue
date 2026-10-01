<script setup lang="ts">
import { reactive, ref, watch, onMounted } from 'vue'
import { catsApi } from '@/api/cats'
import type { Cat, CatGender, CatPayload } from '@/types/cat'

const props = defineProps<{
  initial?: Cat | null
  submitLabel?: string
}>()

const emit = defineEmits<{
  (e: 'submit', payload: CatPayload): void
  (e: 'cancel'): void
}>()

const form = reactive<CatPayload>({
  name: '',
  gender: 'female',
  age: 1,
  mother_id: null,
  father_ids: [],
})

const mothers = ref<Cat[]>([])
const fathers = ref<Cat[]>([])
const errors = ref<string | null>(null)

watch(
  () => props.initial,
  (cat) => {
    if (!cat) return
    form.name = cat.name
    form.gender = cat.gender
    form.age = cat.age
    form.mother_id = cat.mother_id
    form.father_ids = cat.father_ids ?? []
  },
  { immediate: true },
)

onMounted(async () => {
  // Тянем всех кошек, чтобы выбрать родителей.
  // Если у API есть фильтр по полу — используйте его.
  try {
    const data = await catsApi.list({ per_page: 1000 })
    mothers.value = data.items.filter((c) => c.gender === 'female')
    fathers.value = data.items.filter((c) => c.gender === 'male')
  } catch {
    // молча — родители необязательны
  }
})

function submit() {
  errors.value = null
  if (!form.name.trim()) {
    errors.value = 'Укажите кличку'
    return
  }
  emit('submit', { ...form, father_ids: [...(form.father_ids ?? [])] })
}
</script>

<template>
  <form class="row g-3" @submit.prevent="submit">
    <div v-if="errors" class="alert alert-danger">{{ errors }}</div>

    <div class="col-md-6">
      <label class="form-label">Кличка</label>
      <input v-model="form.name" type="text" class="form-control" maxlength="15" />
    </div>

    <div class="col-md-3">
      <label class="form-label">Пол</label>
      <select v-model="form.gender" class="form-select">
        <option value="female">Кошка</option>
        <option value="male">Кот</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Возраст (лет)</label>
      <input v-model.number="form.age" type="number" min="1" max="30" class="form-control" />
    </div>

    <div class="col-md-6">
      <label class="form-label">Мать</label>
      <select v-model="form.mother_id" class="form-select">
        <option :value="null">— не указана —</option>
        <option v-for="m in mothers" :key="m.id" :value="m.id">{{ m.name }}</option>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Отцы (можно несколько)</label>
      <select v-model="form.father_ids" class="form-select" multiple size="4">
        <option v-for="f in fathers" :key="f.id" :value="f.id">{{ f.name }}</option>
      </select>
      <div class="form-text">Ctrl/Cmd — выбрать несколько</div>
    </div>

    <div class="col-12 d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        {{ submitLabel ?? 'Сохранить' }}
      </button>
      <button type="button" class="btn btn-outline-secondary" @click="emit('cancel')">
        Отмена
      </button>
    </div>
  </form>
</template>
