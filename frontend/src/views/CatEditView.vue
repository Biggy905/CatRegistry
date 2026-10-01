<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { catsApi } from '@/api/cats'
import type { Cat, CatPayload } from '@/types/cat'
import CatForm from '@/components/CatForm.vue'
import { useNotificationsStore } from '@/stores/notifications'

const props = defineProps<{ id: string }>()
const router = useRouter()
const notifications = useNotificationsStore()

const cat = ref<Cat | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    cat.value = await catsApi.get(Number(props.id))
  } catch {
    // Ошибку уже показал интерцептор
  } finally {
    loading.value = false
  }
})

async function onSubmit(payload: CatPayload) {
  try {
    await catsApi.update(Number(props.id), payload)
    notifications.success('Изменения сохранены')
    router.push({ name: 'cats-detail', params: { id: props.id } })
  } catch {
    // Ошибку уже показал интерцептор
  }
}
</script>

<template>
  <div class="container py-4">
    <h1 class="h3 mb-4">Редактирование кошки</h1>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border"></div>
    </div>
    <CatForm
      v-else-if="cat"
      :initial="cat"
      submit-label="Сохранить"
      @submit="onSubmit"
      @cancel="router.push({ name: 'cats-detail', params: { id } })"
    />
  </div>
</template>
