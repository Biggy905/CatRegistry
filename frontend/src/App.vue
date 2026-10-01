<script setup lang="ts">
import { RouterView, RouterLink } from 'vue-router'
import NotificationHost from '@/components/NotificationHost.vue'
</script>

<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom mb-4">
    <div class="container">
      <RouterLink class="navbar-brand" :to="{ name: 'cats-list' }">
        Реестр кошек
      </RouterLink>
    </div>
  </nav>

  <RouterView />

  <NotificationHost />
</template>
