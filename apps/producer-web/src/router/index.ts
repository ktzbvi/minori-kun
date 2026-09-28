import { createRouter, createWebHistory } from 'vue-router'
import { queryClient } from '@/lib/query'
import { currentSessionQuery } from '@/services/auth/auth.query'
import HomePage from '@/pages/HomePage.vue'
import LoginPage from '@/pages/LoginPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginPage, meta: { public: true } },
    { path: '/', name: 'home', component: HomePage },
  ],
})

router.beforeEach(async (to) => {
  if (to.meta.public) return true
  try {
    await queryClient.ensureQueryData(currentSessionQuery)
    return true
  } catch {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
})
