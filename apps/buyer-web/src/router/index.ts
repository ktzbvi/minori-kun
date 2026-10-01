import { createRouter, createWebHistory } from 'vue-router'
import { currentSessionQuery, queryClient } from '@/lib/query'
import HomePage from '@/pages/HomePage.vue'
import CategoryPage from '@/pages/CategoryPage.vue'
import CategoryProductListPage from '@/pages/CategoryProductListPage.vue'
import CartPage from '@/pages/CartPage.vue'
import ContactCompletePage from '@/pages/ContactCompletePage.vue'
import ContactPage from '@/pages/ContactPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import MyPage from '@/pages/MyPage.vue'
import MemberInfoPage from '@/pages/MemberInfoPage.vue'
import PasswordChangePage from '@/pages/PasswordChangePage.vue'
import PasswordResetConfirmPage from '@/pages/PasswordResetConfirmPage.vue'
import PasswordResetPage from '@/pages/PasswordResetPage.vue'
import OrderConfirmationPage from '@/pages/OrderConfirmationPage.vue'
import ProductDetailPage from '@/pages/ProductDetailPage.vue'
import RegisterEmailPage from '@/pages/RegisterEmailPage.vue'
import RegisterOtpPage from '@/pages/RegisterOtpPage.vue'
import RegisterDetailsPage from '@/pages/RegisterDetailsPage.vue'
import SearchPage from '@/pages/SearchPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginPage, meta: { public: true } },
    { path: '/my-page', name: 'my-page', component: MyPage },
    {
      path: '/my-page/contact',
      name: 'contact',
      component: ContactPage,
    },
    {
      path: '/my-page/contact/complete',
      name: 'contact-complete',
      component: ContactCompletePage,
    },
    {
      path: '/my-page/member-info',
      name: 'member-info',
      component: MemberInfoPage,
    },
    {
      path: '/my-page/password',
      name: 'password-change',
      component: PasswordChangePage,
    },
    {
      path: '/password-reset',
      name: 'password-reset',
      component: PasswordResetPage,
      meta: { public: true },
    },
    {
      path: '/password-reset/confirm',
      name: 'password-reset-confirm',
      component: PasswordResetConfirmPage,
      meta: { public: true },
    },
    { path: '/register', name: 'register', component: RegisterEmailPage, meta: { public: true } },
    {
      path: '/register/verify',
      name: 'register-verify',
      component: RegisterOtpPage,
      meta: { public: true },
    },
    {
      path: '/register/details',
      name: 'register-details',
      component: RegisterDetailsPage,
      meta: { public: true },
    },
    { path: '/', name: 'home', component: HomePage, meta: { public: true } },
    { path: '/categories', name: 'categories', component: CategoryPage, meta: { public: true } },
    { path: '/cart', name: 'cart', component: CartPage, meta: { public: true } },
    {
      path: '/order-confirmation',
      name: 'order-confirmation',
      component: OrderConfirmationPage,
      meta: { public: true },
    },
    {
      path: '/categories/:categoryId',
      name: 'category-products',
      component: CategoryProductListPage,
      meta: { public: true },
    },
    {
      path: '/products/:productId',
      name: 'product-detail',
      component: ProductDetailPage,
      meta: { public: true },
    },
    {
      path: '/search',
      name: 'search',
      component: SearchPage,
      meta: { public: true },
    },
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
