<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { RouterLink } from 'vue-router'
import { useCatsStore } from '@/stores/cats'
import type { CatFilters } from '@/types/cat'
import CatCard from '@/components/CatCard.vue'
import CatPagination from '@/components/CatPagination.vue'
import CatFiltersBar from '@/components/CatFilters.vue'

const store = useCatsStore()
const { items, total, page, limit, loading, filters } = storeToRefs(store)

onMounted(() => store.fetchList())

watch(page, () => store.fetchList())
watch(limit, () => store.fetchList())

function onFiltersUpdate(v: CatFilters) {
  filters.value = v
}

function onFiltersApply() {
  store.applyFilters()
}

function onFiltersReset() {
  store.resetFilters()
}
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0">Кошки</h1>
      <RouterLink :to="{ name: 'cats-create' }" class="btn btn-primary">
        + Добавить
      </RouterLink>
    </div>

    <CatFiltersBar
      :model-value="filters"
      @update:model-value="onFiltersUpdate"
      @apply="onFiltersApply"
      @reset="onFiltersReset"
    />

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status"></div>
    </div>

    <div v-else-if="!items.length" class="alert alert-info d-flex justify-content-between align-items-center">
      <span>Ничего не найдено.</span>
      <RouterLink :to="{ name: 'cats-create' }" class="btn btn-sm btn-primary">
        Добавить первую
      </RouterLink>
    </div>

    <div v-else class="row g-3">
      <div v-for="cat in items" :key="cat.id" class="col-md-6 col-lg-4">
        <CatCard :cat="cat" />
      </div>
    </div>

    <CatPagination
      :page="page"
      :limit="limit"
      :total="total"
      @update:page="store.setPage($event)"
      @update:limit="store.setLimit($event)"
    />
  </div>
</template>
