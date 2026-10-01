import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import { catsApi } from '@/api/cats'
import type { Cat, CatFilters } from '@/types/cat'

const DEFAULT_LIMIT = 20

export const useCatsStore = defineStore('cats', () => {
  const items = ref<Cat[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(DEFAULT_LIMIT)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const filters = reactive<CatFilters>({
    gender: null,
    age_range: null,
  })

  async function fetchList() {
    loading.value = true
    error.value = null

    try {
      const data = await catsApi.list({
        page: page.value,
        limit: limit.value,
        gender: filters.gender,
        age_range: filters.age_range,
      })

      items.value = Array.isArray(data?.items) ? data.items : []
      total.value = typeof data?.total === 'number' ? data.total : items.value.length
    } catch (e) {
      // Ошибку уже показал интерцептор — здесь только логируем
      error.value = e instanceof Error ? e.message : String(e)
      items.value = []
      total.value = 0
    } finally {
      loading.value = false
    }
  }

  function setPage(p: number) {
    if (p < 1) return
    page.value = p
    return fetchList()
  }

  function setLimit(l: number) {
    limit.value = l
    page.value = 1
    return fetchList()
  }

  function applyFilters() {
    page.value = 1
    return fetchList()
  }

  function resetFilters() {
    filters.gender = null
    filters.age_range = null
    page.value = 1
    return fetchList()
  }

  return {
    items, total, page, limit, loading, error, filters,
    fetchList, setPage, setLimit, applyFilters, resetFilters,
  }
})
