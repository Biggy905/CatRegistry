import axios, { AxiosError, type AxiosResponse } from 'axios'
import { useNotificationsStore } from '@/stores/notifications'

/**
 * Конверт успешного ответа от бэкенда.
 */
export interface ApiEnvelope<T> {
  code: number
  data: T
}

/**
 * Возможные конверты ошибок от бэкенда.
 */
export interface ApiErrorEnvelope {
  code: number
  name?: string
  message?: string | { message?: string; name?: string; [key: string]: unknown }
  errors?: Record<string, string[]>
}

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  timeout: 15000,
  headers: { 'Content-Type': 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

/**
 * Достаёт человекочитаемое сообщение из любого формата ошибки бэкенда.
 */
function extractMessage(payload: unknown): string {
  if (!payload || typeof payload !== 'object') {
    return 'Неизвестная ошибка'
  }

  const p = payload as ApiErrorEnvelope

  // 1. errors: { field: ['msg', ...], ... } — ошибки валидации
  if (p.errors && typeof p.errors === 'object') {
    const firstField = Object.keys(p.errors)[0]
    if (firstField) {
      const messages = p.errors[firstField]
      if (Array.isArray(messages) && messages.length > 0) {
        return messages[0]
      }
    }
  }

  // 2. message — строка
  if (typeof p.message === 'string' && p.message.length > 0) {
    return p.message
  }

  // 3. message — объект (Yii2 convertExceptionToArray)
  if (p.message && typeof p.message === 'object') {
    const inner = p.message as { message?: string; name?: string }
    if (typeof inner.message === 'string') {
      return inner.message
    }
  }

  // 4. fallback по HTTP-коду
  if (typeof p.code === 'number') {
    return `Ошибка ${p.code}`
  }

  return 'Неизвестная ошибка'
}

http.interceptors.response.use(
  (response: AxiosResponse<ApiEnvelope<unknown>>) => {
    // Успешный ответ — разворачиваем конверт
    const envelope = response.data

    if (
      envelope &&
      typeof envelope === 'object' &&
      'code' in envelope &&
      'data' in envelope
    ) {
      // Заменяем data в response на содержимое конверта.
      // Теперь response.data = то, что было в envelope.data.
      response.data = envelope.data as never
    }

    return response
  },
  (error: AxiosError<ApiErrorEnvelope>) => {
    const notifications = useNotificationsStore()
    const status = error.response?.status
    const payload = error.response?.data

    const message = extractMessage(payload)

    // Спец-обработка по статусам
    if (status === 401) {
      notifications.warning('Сессия истекла, войдите заново')
    } else if (status === 404) {
      notifications.warning(message, { title: 'Не найдено' })
    } else if (status === 400 || status === 422) {
      notifications.error(message, { title: 'Ошибка валидации' })
    } else if (status && status >= 500) {
      notifications.error(message, { title: `Ошибка сервера (${status})` })
    } else if (!error.response) {
      notifications.error('Сервер недоступен', { title: 'Сеть' })
    } else {
      notifications.error(message)
    }

    return Promise.reject(new Error(message))
  },
)
