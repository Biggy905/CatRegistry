<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  page: number
  limit: number
  total: number
}>()

const emit = defineEmits<{
  (e: 'update:page', value: number): void
  (e: 'update:limit', value: number): void
}>()

const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.limit)))

const pages = computed<(number | '…')[]>(() => {
  const tp = totalPages.value
  const cur = props.page
  const result: (number | '…')[] = []

  if (tp <= 7) {
    for (let i = 1; i <= tp; i++) result.push(i)
    return result
  }

  result.push(1)
  if (cur > 3) result.push('…')
  for (let i = Math.max(2, cur - 1); i <= Math.min(tp - 1, cur + 1); i++) result.push(i)
  if (cur < tp - 2) result.push('…')
  result.push(tp)
  return result
})

const limitOptions = [10, 20, 50, 100]

function go(p: number) {
  if (p < 1 || p > totalPages.value || p === props.page) return
  emit('update:page', p)
}

function changeLimit(e: Event) {
  const value = Number((e.target as HTMLSelectElement).value)
  emit('update:limit', value)
}
</script>

<template>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
    <div class="text-muted small">
      Всего: {{ total }}
    </div>

    <nav v-if="totalPages > 1">
      <ul class="pagination mb-0">
        <li class="page-item" :class="{ disabled: page === 1 }">
          <button class="page-link" @click="go(page - 1)">«</button>
        </li>
        <li
          v-for="(p, i) in pages"
          :key="i"
          class="page-item"
          :class="{ active: p === page, disabled: p === '…' }"
        >
          <button
            v-if="p !== '…'"
            class="page-link"
            @click="go(p as number)"
          >{{ p }}</button>
          <span v-else class="page-link">…</span>
        </li>
        <li class="page-item" :class="{ disabled: page === totalPages }">
          <button class="page-link" @click="go(page + 1)">»</button>
        </li>
      </ul>
    </nav>

    <div class="d-flex align-items-center gap-2">
      <label class="form-label mb-0 small text-muted">На странице:</label>
      <select class="form-select form-select-sm" :value="limit" @change="changeLimit">
        <option v-for="opt in limitOptions" :key="opt" :value="opt">{{ opt }}</option>
      </select>
    </div>
  </div>
</template>
