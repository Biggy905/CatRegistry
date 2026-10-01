<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  page: number
  perPage: number
  total: number
}>()

const emit = defineEmits<{
  (e: 'update:page', value: number): void
}>()

const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)))

const pages = computed(() => {
  const result: (number | '…')[] = []
  const tp = totalPages.value
  const cur = props.page
  const add = (n: number | '…') => result.push(n)

  if (tp <= 7) {
    for (let i = 1; i <= tp; i++) add(i)
  } else {
    add(1)
    if (cur > 3) add('…')
    for (let i = Math.max(2, cur - 1); i <= Math.min(tp - 1, cur + 1); i++) add(i)
    if (cur < tp - 2) add('…')
    add(tp)
  }
  return result
})

function go(p: number) {
  if (p < 1 || p > totalPages.value || p === props.page) return
  emit('update:page', p)
}
</script>

<template>
  <nav v-if="totalPages > 1" class="mt-4">
    <ul class="pagination justify-content-center">
      <li class="page-item" :class="{ disabled: page === 1 }">
        <button class="page-link" @click="go(page - 1)">«</button>
      </li>
      <li
        v-for="(p, i) in pages"
        :key="i"
        class="page-item"
        :class="{ active: p === page, disabled: p === '…' }"
      >
        <button v-if="p !== '…'" class="page-link" @click="go(p as number)">{{ p }}</button>
        <span v-else class="page-link">…</span>
      </li>
      <li class="page-item" :class="{ disabled: page === totalPages }">
        <button class="page-link" @click="go(page + 1)">»</button>
      </li>
    </ul>
  </nav>
</template>
