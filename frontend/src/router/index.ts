import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/cats' },
    {
      path: '/cats',
      name: 'cats-list',
      component: () => import('@/views/CatListView.vue'),
    },
    {
      path: '/cats/create',
      name: 'cats-create',
      component: () => import('@/views/CatCreateView.vue'),
    },
    {
      path: '/cats/:id(\\d+)',
      name: 'cats-detail',
      component: () => import('@/views/CatDetailView.vue'),
      props: true,
    },
    {
      path: '/cats/:id(\\d+)/edit',
      name: 'cats-edit',
      component: () => import('@/views/CatEditView.vue'),
      props: true,
    },
  ],
})

export default router
