import { http } from './http'
import type {
  Cat,
  CatListQuery,
  CatListResponse,
  CatPayload,
} from '@/types/cat'

function toQueryParams(q: CatListQuery): Record<string, unknown> {
  const params: Record<string, unknown> = {}

  if (q.page !== undefined) params.page = q.page
  if (q.limit !== undefined) params.limit = q.limit
  if (q.gender) params.gender = q.gender

  if (q.age) {
    params.age = q.age
  }

  return params
}

export interface CatSearchQuery {
  name: string
  gender: 'male' | 'female'
  exclude_cat_id: number
}

export interface CatSearchResponse {
  items: Pick<Cat, 'id' | 'name' | 'gender' | 'age'>[]
}

export const catsApi = {
  async list(query: CatListQuery = {}): Promise<CatListResponse> {
    const { data } = await http.get<CatListResponse>('/cats', {
      params: toQueryParams(query),
      paramsSerializer: {
        serialize: (params: Record<string, unknown>) => {
          const parts: string[] = []

          for (const [key, value] of Object.entries(params)) {
            if (value === undefined || value === null || value === '') continue

            if (Array.isArray(value)) {
              for (const v of value) {z
                parts.push(
                  `${encodeURIComponent(key)}[]=${encodeURIComponent(String(v))}`,
                )
              }
            } else {
              parts.push(
                `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`,
              )
            }
          }

          return parts.join('&')
        },
      },
    })
    return data
  },

  async get(id: number): Promise<Cat> {
    const { data } = await http.get<Cat | Cat[]>(`/cat/${id}`)

    const cat = Array.isArray(data) ? data[0] : data

    if (!cat) {
      throw new Error(`Кошка #${id} не найдена`)
    }

    return cat
  },

  async search(query: CatSearchQuery): Promise<CatSearchResponse> {
    const { data } = await http.get<CatSearchResponse>('/cats/search', {
      params: {
        name: query.name,
        gender: query.gender,
        exclude_cat_id: query.exclude_cat_id,
      },
    })
    return data
  },

  async create(payload: CatPayload): Promise<Cat> {
    const { data } = await http.post<Cat>('/cats', payload)
    return data
  },

  async update(id: number, payload: Partial<CatPayload>): Promise<Cat> {
    const { data } = await http.put<Cat>(`/cats/${id}`, payload)
    return data
  },

  async remove(id: number): Promise<void> {
    await http.delete(`/cats/${id}`)
  },
}
