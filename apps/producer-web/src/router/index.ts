import { createRouter, createWebHistory } from 'vue-router'
import { queryClient } from '@/lib/query'
import { currentSessionQueryOptions } from '@/services/auth/auth.query'
import ProducerPortalLayout from '@/components/layout/ProducerPortalLayout.vue'
import DashboardPage from '@/pages/DashboardPage.vue'
import EntryPage from '@/pages/EntryPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import PasswordResetPage from '@/pages/PasswordResetPage.vue'
import OnboardingPage from '@/pages/OnboardingPage.vue'
import ProductsPage from '@/pages/ProductsPage.vue'
import ProductFormPage from '@/pages/ProductFormPage.vue'
import PortalUnavailablePage from '@/pages/PortalUnavailablePage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import RegisterVerifyPage from '@/pages/RegisterVerifyPage.vue'
import RegisterDetailsPage from '@/pages/RegisterDetailsPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/password-reset',
      name: 'password-reset',
      component: PasswordResetPage,
      meta: { public: true },
    },
    {
      path: '/password-reset/confirm',
      name: 'password-reset-confirm',
      component: PasswordResetPage,
      meta: { public: true },
    },
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
    {
      path: '/onboarding',
      name: 'onboarding',
      component: OnboardingPage,
      beforeEnter: async () => {
        try {
          const session = await queryClient.fetchQuery(currentSessionQueryOptions())
          if (session.role !== 'producer') return { name: 'login' }
          return session.producer?.eligible_to_sell ? { name: 'dashboard' } : true
        } catch {
          return { name: 'login', query: { redirect: '/onboarding' } }
        }
      },
    },
    { path: '/', name: 'entry', component: EntryPage, meta: { public: true } },
    {
      path: '/',
      component: ProducerPortalLayout,
      beforeEnter: async (to) => {
        try {
          const session = await queryClient.fetchQuery(currentSessionQueryOptions())
          if (session.role !== 'producer') return { name: 'login' }
          if (session.producer?.eligible_to_sell !== true) return { name: 'onboarding' }
          return true
        } catch {
          return { name: 'login', query: { redirect: to.fullPath } }
        }
      },
      children: [
        {
          path: 'dashboard',
          name: 'dashboard',
          component: DashboardPage,
          meta: { portalTitle: 'ダッシュボード', activeRoute: '/dashboard' },
        },
        {
          path: 'products',
          name: 'products',
          component: ProductsPage,
          meta: { portalTitle: '商品管理', activeRoute: '/products' },
        },
        {
          path: 'products/new',
          name: 'product-create',
          component: ProductFormPage,
          meta: { portalTitle: '商品を登録', activeRoute: '/products' },
        },
        {
          path: 'products/:id',
          name: 'product-edit',
          component: ProductFormPage,
          meta: { portalTitle: '商品を編集', activeRoute: '/products' },
        },
        {
          path: 'orders',
          name: 'orders',
          component: PortalUnavailablePage,
          props: { title: '注文管理' },
          meta: { portalTitle: '注文管理', activeRoute: '/orders' },
        },
        {
          path: 'sales',
          name: 'sales',
          component: PortalUnavailablePage,
          props: { title: '売上・振込' },
          meta: { portalTitle: '売上・振込', activeRoute: '/sales' },
        },
      ],
    },
  ],
})
