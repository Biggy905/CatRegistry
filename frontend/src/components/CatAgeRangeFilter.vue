<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import Slider from '@vueform/slider'
import '@vueform/slider/themes/default.css'

const props = defineProps<{
  modelValue: [number, number] | null
  min?: number
  max?: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: [number, number] | null): void
}>()

const MIN = props.min ?? 0
const MAX = props.max ?? 30

const local = ref<[number, number]>(
  props.modelValue ?? [MIN, MAX]
)

watch(
  () => props.modelValue,
  (v) => {
    local.value = v ?? [MIN, MAX]
  }
)

const isFullRange = computed(
  () => local.value[0] === MIN && local.value[1] === MAX
)

function onChange(value: number | number[]) {
  if (Array.isArray(value)) {
    local.value = [value[0], value[1]]
    emit(
      'update:modelValue',
      isFullRange.value ? null : ([...local.value] as [number, number])
    )
  }
}

function reset() {
  local.value = [MIN, MAX]
  emit('update:modelValue', null)
}
</script>

<template>
  <div class="age-range-filter">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <label class="form-label mb-0">Возраст</label>
      <button
        v-if="!isFullRange"
        type="button"
        class="btn btn-link btn-sm p-0 text-decoration-none"
        @click="reset"
      >
        сбросить
      </button>
    </div>

    <Slider
      v-model="local"
      :min="MIN"
      :max="MAX"
      :tooltips="true"
      :lazy="false"
      @change="onChange"
    />

    <div class="d-flex justify-content-between small text-muted mt-1">
      <span>{{ local[0] }} лет</span>
      <span>{{ local[1] }} лет</span>
    </div>
  </div>
</template>
