<script setup lang="ts">
import { useRouter } from 'vue-router'
import { catsApi } from '@/api/cats'
import type { CatPayload } from '@/types/cat'
import CatForm from '@/components/CatForm.vue'

const router = useRouter()

async function onSubmit(payload: CatPayload) {
  const cat = await catsApi.create(payload)
  router.push({ name: 'cats-detail', params: { id: cat.id } })
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
