<script setup lang="ts">
import { reactive, watch } from 'vue'
import type { CatFilters } from '@/types/cat'
import CatAgeRangeFilter from './CatAgeRangeFilter.vue'

const props = defineProps<{
  modelValue: CatFilters
  minAge?: number
  maxAge?: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: CatFilters): void
  (e: 'apply'): void
  (e: 'reset'): void
}>()

const local = reactive<CatFilters>({
  gender: props.modelValue.gender,
  age_range: props.modelValue.age_range,
})

watch(
  () => props.modelValue,
  (v) => {
    local.gender = v.gender
    local.age_range = v.age_range
  },
  { deep: true },
)

function emitChange() {
  emit('update:modelValue', {
    gender: local.gender,
    age_range: local.age_range,
  })
}
</script>

<template>
  <div class="card mb-3">
    <div class="card-body row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Пол</label>
        <select v-model="local.gender" class="form-select" @change="emitChange">
          <option :value="null">Все</option>
          <option value="female">Кошки</option>
          <option value="male">Коты</option>
        </select>
      </div>

      <div class="col-md-5">
        <CatAgeRangeFilter
          :model-value="local.age_range"
          :min="minAge ?? 0"
          :max="maxAge ?? 30"
          @update:model-value="(v) => { local.age_range = v; emitChange() }"
        />
      </div>

      <div class="col-md-4 d-flex gap-2 justify-content-md-end">
        <button class="btn btn-primary" @click="emit('apply')">Применить</button>
        <button class="btn btn-outline-secondary" @click="emit('reset')">Сбросить</button>
      </div>
    </div>
  </div>
</template>
