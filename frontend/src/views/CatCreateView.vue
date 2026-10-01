<script setup lang="ts">
import { useRouter } from 'vue-router'
import { catsApi } from '@/api/cats'
import type { CatPayload } from '@/types/cat'
import CatForm from '@/components/CatForm.vue'
import { useNotificationsStore } from '@/stores/notifications'

const router = useRouter()
const notifications = useNotificationsStore()

async function onSubmit(payload: CatPayload) {
  try {
    const cat = await catsApi.create(payload)
    notifications.success(`Кошка «${cat.name}» создана`)
    router.push({ name: 'cats-detail', params: { id: cat.id } })
  } catch {
    // Ошибку уже показал интерцептор
  }
}
</script>

<template>
  <div class="container py-4">
    <h1 class="h3 mb-4">Новая кошка</h1>
    <CatForm
      submit-label="Создать"
      @submit="onSubmit"
      @cancel="router.push({ name: 'cats-list' })"
    />
  </div>
</template>
