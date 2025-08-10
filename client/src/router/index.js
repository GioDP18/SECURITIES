import { createRouter, createWebHistory } from 'vue-router'
import List from '../views/List.vue'
import Add from '../views/Add.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'list',
      component: List,
    },
    {
      path: '/add',
      name: 'add',
      component: Add,
    },
  ],
})

export default router
