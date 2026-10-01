export type CatGender = 'male' | 'female'

export interface CatFlat {
  id: number
  name: string
  gender: CatGender
  age: number
  created_at?: string | null
  updated_at?: string | null
}

export interface Cat extends CatFlat {
  mother: CatFlat | null
  fathers: CatFlat[] | null   // ← бэк может вернуть null
}

export interface CatListItem extends CatFlat {}

export interface CatFilters {
  gender: CatGender | null
  age_range: [number, number] | null
}

export interface CatListQuery extends Partial<CatFilters> {
  page?: number
  limit?: number
}

export interface CatListResponse {
  items: CatListItem[]
  total: number
  page: number
  limit: number
}

export interface CatPayload {
  name: string
  gender: CatGender
  age: number
  mother_id?: number | null
  father_ids?: number[]
}
