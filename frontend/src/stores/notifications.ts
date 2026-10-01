import { defineStore } from 'pinia'
import { ref } from 'vue'

export type NotificationType = 'success' | 'error' | 'warning' | 'info'

export interface AppNotification {
  id: number
  type: NotificationType
  message: string
  title?: string
  timeout: number // ms, 0 = не закрывать автоматически
}

let counter = 0

export const useNotificationsStore = defineStore('notifications', () => {
  const items = ref<AppNotification[]>([])

  function push(
    message: string,
    type: NotificationType = 'info',
    options: { title?: string; timeout?: number } = {},
  ) {
    const id = ++counter
    const defaultTimeout = type === 'error' ? 8000 : 4000

    items.value.push({
      id,
      type,
      message,
      title: options.title,
      timeout: options.timeout ?? defaultTimeout,
    })

    return id
  }

  function remove(id: number) {
    items.value = items.value.filter((n) => n.id !== id)
  }

  function clear() {
    items.value = []
  }

  // Шорткаты
  const success = (msg: string, opts?: { title?: string; timeout?: number }) =>
    push(msg, 'success', opts)
  const error = (msg: string, opts?: { title?: string; timeout?: number }) =>
    push(msg, 'error', opts)
  const warning = (msg: string, opts?: { title?: string; timeout?: number }) =>
    push(msg, 'warning', opts)
  const info = (msg: string, opts?: { title?: string; timeout?: number }) =>
    push(msg, 'info', opts)

  return { items, push, remove, clear, success, error, warning, info }
})
