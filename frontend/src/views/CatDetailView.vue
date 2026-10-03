<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { catsApi } from '@/api/cats'
import type { Cat } from '@/types/cat'
import { useNotificationsStore } from '@/stores/notifications'

const props = defineProps<{ id: string }>()
const router = useRouter()
const notifications = useNotificationsStore()

const cat = ref<Cat | null>(null)
const loading = ref(true)
const removing = ref(false)

onMounted(load)

async function load() {
  loading.value = true
  try {
    cat.value = await catsApi.get(Number(props.id))
  } catch {
    // Ошибку покажет интерцептор
  } finally {
    loading.value = false
  }
}

async function remove() {
  if (!cat.value) return
  if (!confirm(`Удалить «${cat.value.name}»?`)) return

  removing.value = true
  try {
    await catsApi.remove(cat.value.id)
    notifications.success('Кошка удалена')
    router.push({ name: 'cats-list' })
  } catch {
    // Ошибку покажет интерцептор
  } finally {
    removing.value = false
  }
}
</script>

<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border"></div>
    </div>

    <div v-else-if="cat">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ cat.name }}</h1>
        <div class="d-flex gap-2">
          <RouterLink
            :to="{ name: 'cats-edit', params: { id: cat.id } }"
            class="btn btn-outline-primary"
          >Изменить</RouterLink>
          <button class="btn btn-outline-danger" :disabled="removing" @click="remove">
            {{ removing ? 'Удаление…' : 'Удалить' }}
          </button>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3">Пол</dt>
            <dd class="col-sm-9">{{ cat.gender === 'female' ? 'Кошка' : 'Кот' }}</dd>

            <dt class="col-sm-3">Возраст</dt>
            <dd class="col-sm-9">{{ cat.age }} лет</dd>

            <dt class="col-sm-3">Мать</dt>
            <dd class="col-sm-9">
              <RouterLink
                v-if="cat.mother"
                :to="{ name: 'cats-detail', params: { id: cat.mother.id } }"
              >{{ cat.mother.name }}</RouterLink>
              <span v-else class="text-muted">не указана</span>
            </dd>

            <dt class="col-sm-3">Отцы</dt>
            <dd class="col-sm-9">
              <template v-if="cat.fathers?.length">
                <RouterLink
                  v-for="f in cat.fathers"
                  :key="f.id"
                  :to="{ name: 'cats-detail', params: { id: f.id } }"
                  class="badge text-bg-secondary me-1 text-decoration-none"
                >{{ f.name }}</RouterLink>
              </template>
              <span v-else class="text-muted">не указаны</span>
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>
