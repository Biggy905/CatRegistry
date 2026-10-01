<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import VueSelect from '@ttbooking/vue-select'
import '@ttbooking/vue-select/style.css'
import { catsApi } from '@/api/cats'
import type { Cat, CatPayload } from '@/types/cat'
import { debounce } from 'lodash-es'

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

const errors = ref<string | null>(null)

// Опции для селектов
const motherOptions = ref<{ id: number; text: string }[]>([])
const fatherOptions = ref<{ id: number; text: string }[]>([])
const mothersLoading = ref(false)
const fathersLoading = ref(false)

// Заполняем форму при редактировании
watch(
  () => props.initial,
  (cat) => {
    if (!cat) return

    form.name = cat.name
    form.gender = cat.gender
    form.age = cat.age

    // mother — объект или null
    form.mother_id = cat.mother?.id ?? null

    // fathers может быть null — приводим к массиву
    const fathers = cat.fathers ?? []
    form.father_ids = fathers.map((f) => f.id)

    // Предзаполняем options
    motherOptions.value = cat.mother
      ? [{ id: cat.mother.id, text: cat.mother.name }]
      : []

    fatherOptions.value = fathers.map((f) => ({ id: f.id, text: f.name }))
  },
  { immediate: true },
)

// Асинхронный поиск матерей
const searchMothers = debounce(async (search: string) => {
  mothersLoading.value = true
  try {
    const data = await catsApi.search({
      name: search,
      gender: 'female',
      exclude_cat_id: props.initial?.id ?? 0,
    })
    motherOptions.value = data.items.map((c) => ({ id: c.id, text: c.name }))
  } catch {
    motherOptions.value = []
  } finally {
    mothersLoading.value = false
  }
}, 300)

// Асинхронный поиск отцов
const searchFathers = debounce(async (search: string) => {
  fathersLoading.value = true
  try {
    const data = await catsApi.search({
      name: search,
      gender: 'male',
      exclude_cat_id: props.initial?.id ?? 0,
    })
    fatherOptions.value = data.items.map((c) => ({ id: c.id, text: c.name }))
  } catch {
    fatherOptions.value = []
  } finally {
    fathersLoading.value = false
  }
}, 300)

// При открытии дропдауна грузим начальный список (пустой запрос)
function onMotherSearchOpen() {
  if (!motherOptions.value.length) searchMothers('')
}
function onFatherSearchOpen() {
  if (!fatherOptions.value.length) searchFathers('')
}

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

    <!-- Мать -->
    <div class="col-md-6">
      <label class="form-label">Мать</label>
      <VueSelect
        v-model="form.mother_id"
        :options="motherOptions"
        :loading="mothersLoading"
        lang="ru"
        placeholder="Начните вводить кличку..."
        :allow-clear="true"
        @search="searchMothers"
        @open="onMotherSearchOpen"
      />
    </div>

    <!-- Отцы -->
    <div class="col-md-6">
      <label class="form-label">Отцы (можно несколько)</label>
      <VueSelect
        v-model="form.father_ids"
        :options="fatherOptions"
        :loading="fathersLoading"
        :multiple="true"
        lang="ru"
        placeholder="Начните вводить клички..."
        @search="searchFathers"
        @open="onFatherSearchOpen"
      />
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
