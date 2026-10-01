<script setup lang="ts">
import type { Cat } from '@/types/cat'
import { RouterLink } from 'vue-router'

defineProps<{ cat: Cat }>()
</script>

<template>
  <div class="card h-100">
    <div class="card-body d-flex flex-column">
      <h5 class="card-title mb-1">{{ cat.name }}</h5>
      <p class="text-muted small mb-2">
        {{ cat.gender === 'female' ? 'Кошка' : 'Кот' }}
        , {{ cat.age }} {{ plural(cat.age, 'год', 'года', 'лет') }}
      </p>
      <div class="mt-auto d-flex gap-2">
        <RouterLink
          :to="{ name: 'cats-detail', params: { id: cat.id } }"
          class="btn btn-sm btn-outline-primary"
        >Открыть</RouterLink>
        <RouterLink
          :to="{ name: 'cats-edit', params: { id: cat.id } }"
          class="btn btn-sm btn-outline-secondary"
        >Изменить</RouterLink>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
function plural(n: number, one: string, few: string, many: string): string {
  const m10 = n % 10
  const m100 = n % 100
  if (m10 === 1 && m100 !== 11) return one
  if (m10 >= 2 && m10 <= 4 && (m100 < 10 || m100 >= 20)) return few
  return many
}
export default { methods: { plural } }
</script>
