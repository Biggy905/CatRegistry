import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import { catsApi } from '@/api/cats'
import type { Cat, CatFilters } from '@/types/cat'

export const useCatsStore = defineStore('cats', () => {
  const items = ref<Cat[]>([])
  const total = ref(0)
  const page = ref(1)
  const perPage = ref(10)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const filters = reactive<CatFilters>({
    gender: null,
    age_from: null,
    age_to: null,
  })

  async function fetchList() {
    loading.value = true
    error.value = null
    try {
      const data = await catsApi.list({
        ...cleanFilters(filters),
        page: page.value,
        per_page: perPage.value,
      })
      items.value = data.items
      total.value = data.total
    } catch (e: any) {
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id: number): Promise<Cat> {
    return catsApi.get(id)
  }

  function cleanFilters(f: CatFilters) {
    return Object.fromEntries(
      Object.entries(f).filter(([, v]) => v !== null && v !== undefined && v !== ''),
    )
  }

  function resetFilters() {
    filters.gender = null
    filters.age_from = null
    filters.age_to = null
    page.value = 1
  }

  return {
    items, total, page, perPage, loading, error, filters,
    fetchList, fetchOne, resetFilters,
  }
})
