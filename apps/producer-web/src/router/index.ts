import { createRouter, createWebHistory } from 'vue-router'
import { queryClient } from '@/lib/query'
import { currentSessionQuery } from '@/services/auth/auth.query'
import EntryPage from '@/pages/EntryPage.vue'
import HomePage from '@/pages/HomePage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import OnboardingPage from '@/pages/OnboardingPage.vue'
import ProductsPage from '@/pages/ProductsPage.vue'
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
    { path: '/onboarding', name: 'onboarding', component: OnboardingPage },
    { path: '/', name: 'entry', component: EntryPage, meta: { public: true } },
    { path: '/dashboard', name: 'dashboard', component: HomePage },
    { path: '/products', name: 'products', component: ProductsPage },
  ],
})

router.beforeEach(async (to) => {
  if (to.meta.public) return true

  try {
    const session = await queryClient.fetchQuery({ ...currentSessionQuery, staleTime: 0 })
    if (session.role !== 'producer') return { name: 'login' }
    const eligible = session.producer?.eligible_to_sell === true
    if (to.name === 'onboarding' && eligible) return { name: 'dashboard' }
    if (to.name !== 'onboarding' && !eligible) return { name: 'onboarding' }
    return true
  } catch {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
})
