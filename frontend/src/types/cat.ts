export type CatGender = 'male' | 'female'

export interface Cat {
  id: number
  name: string
  gender: CatGender
  age: number
  mother_id: number | null
  father_ids?: number[]
  created_at?: number
  updated_at?: number
}

export interface CatFilters {
  gender?: CatGender | null
  age_from?: number | null
  age_to?: number | null
}

export interface CatListResponse {
  items: Cat[]
  total: number
  page: number
  per_page: number
}

export interface CatPayload {
  name: string
  gender: CatGender
  age: number
  mother_id?: number | null
  father_ids?: number[]
}
