import { http } from './http'
import type {
  Cat,
  CatPayload,
  CatFilters,
  CatListResponse,
} from '@/types/cat'

export const catsApi = {
  async list(params: CatFilters & { page?: number; per_page?: number } = {}) {
    const { data } = await http.get<CatListResponse>('/cats', { params })
    return data
  },

  async get(id: number) {
    const { data } = await http.get<Cat>(`/cat/${id}`)
    return data
  },

  async create(payload: CatPayload) {
    const { data } = await http.post<Cat>('/cats', payload)
    return data
  },

  async update(id: number, payload: Partial<CatPayload>) {
    const { data } = await http.put<Cat>(`/cats/${id}`, payload)
    return data
  },

  async remove(id: number) {
    await http.delete(`/cats/${id}`)
  },
}
