<script setup lang="ts">
import { useNotificationsStore } from '@/stores/notifications'
import type { AppNotification } from '@/stores/notifications'

const store = useNotificationsStore()

const iconClass: Record<AppNotification['type'], string> = {
  success: 'bi bi-check-circle-fill text-success',
  error: 'bi bi-exclamation-triangle-fill text-danger',
  warning: 'bi bi-exclamation-circle-fill text-warning',
  info: 'bi bi-info-circle-fill text-primary',
}

const headerClass: Record<AppNotification['type'], string> = {
  success: 'bg-success text-white',
  error: 'bg-danger text-white',
  warning: 'bg-warning text-dark',
  info: 'bg-primary text-white',
}

function onShown(id: number, timeout: number) {
  if (timeout > 0) {
    setTimeout(() => store.remove(id), timeout)
  }
}
</script>

<template>
  <div
    class="toast-container position-fixed top-0 end-0 p-3"
    style="z-index: 1090"
  >
    <div
      v-for="n in store.items"
      :key="n.id"
      class="toast show mb-2"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
      @vue:mounted="() => onShown(n.id, n.timeout)"
    >
      <div class="toast-header" :class="headerClass[n.type]">
        <i :class="iconClass[n.type]" class="me-2"></i>
        <strong class="me-auto">
          {{ n.title ?? defaultTitle(n.type) }}
        </strong>
        <button
          type="button"
          class="btn-close btn-close-white"
          aria-label="Close"
          @click="store.remove(n.id)"
        ></button>
      </div>
      <div class="toast-body">
        {{ n.message }}
      </div>
    </div>
  </div>
</template>

<script lang="ts">
function defaultTitle(type: AppNotification['type']): string {
  switch (type) {
    case 'success':
      return 'Готово'
    case 'error':
      return 'Ошибка'
    case 'warning':
      return 'Внимание'
    default:
      return 'Информация'
  }
}
export default { methods: { defaultTitle } }
</script>
