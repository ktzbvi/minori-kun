import { createRouter, createWebHistory } from 'vue-router'
import { queryClient } from '@/lib/query'
import { currentSessionQuery } from '@/services/auth/auth.query'
import HomePage from '@/pages/HomePage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import RegisterVerifyPage from '@/pages/RegisterVerifyPage.vue'
import RegisterDetailsPage from '@/pages/RegisterDetailsPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginPage, meta: { public: true } },
    { path: '/register', name: 'register', component: RegisterPage, meta: { public: true } },
    {
      path: '/register/verify',
      name: 'register-verify',
      component: RegisterVerifyPage,
      meta: { public: true },
    },
    {
      path: '/register/details',
      name: 'register-details',
      component: RegisterDetailsPage,
      meta: { public: true },
    },
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
