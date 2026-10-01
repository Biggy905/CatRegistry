<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { catsApi } from '@/api/cats'
import type { Cat, CatPayload } from '@/types/cat'
import CatForm from '@/components/CatForm.vue'

const props = defineProps<{ id: string }>()
const router = useRouter()

const cat = ref<Cat | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    cat.value = await catsApi.get(Number(props.id))
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
})

async function onSubmit(payload: CatPayload) {
  await catsApi.update(Number(props.id), payload)
  router.push({ name: 'cats-detail', params: { id: props.id } })
}
</script>

<template>
  <div class="container py-4">
    <h1 class="h3 mb-4">Редактирование кошки</h1>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border"></div>
    </div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <CatForm
      v-else
      :initial="cat"
      submit-label="Сохранить"
      @submit="onSubmit"
      @cancel="router.push({ name: 'cats-detail', params: { id } })"
    />
  </div>
</template>
