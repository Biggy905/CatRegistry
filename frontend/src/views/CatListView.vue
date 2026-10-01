<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink } from 'vue-router'
import { useCatsStore } from '@/stores/cats'
import CatCard from '@/components/CatCard.vue'
import CatPagination from '@/components/CatPagination.vue'

const store = useCatsStore()
const { items, total, page, perPage, loading, error, filters } = storeToRefs(store)

onMounted(() => store.fetchList())

watch(page, () => store.fetchList())
watch(
  () => ({ ...filters.value }),
  () => {
    page.value = 1
    store.fetchList()
  },
  { deep: true },
)
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0">Кошки</h1>
      <RouterLink :to="{ name: 'cats-create' }" class="btn btn-primary">
        + Добавить
      </RouterLink>
    </div>

    <div class="card mb-3">
      <div class="card-body row g-3">
        <div class="col-md-3">
          <label class="form-label">Пол</label>
          <select v-model="filters.gender" class="form-select">
            <option :value="null">Все</option>
            <option value="female">Кошки</option>
            <option value="male">Коты</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Возраст от</label>
          <input v-model.number="filters.age_from" type="number" min="0" class="form-control" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Возраст до</label>
          <input v-model.number="filters.age_to" type="number" min="0" class="form-control" />
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-secondary w-100" @click="store.resetFilters()">
            Сбросить
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status"></div>
    </div>

    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else-if="!items.length" class="alert alert-info">
      Ничего не найдено.
    </div>

    <div v-else class="row g-3">
      <div v-for="cat in items" :key="cat.id" class="col-md-6 col-lg-4">
        <CatCard :cat="cat" />
      </div>
    </div>

    <CatPagination
      v-model:page="page"
      :per-page="perPage"
      :total="total"
    />
  </div>
</template>
