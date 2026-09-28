import { createRouter, createWebHistory } from 'vue-router'
import { currentSessionQuery, queryClient } from '@/lib/query'
import HomePage from '@/pages/HomePage.vue'
import CategoryPage from '@/pages/CategoryPage.vue'
import CategoryProductListPage from '@/pages/CategoryProductListPage.vue'
import CartPage from '@/pages/CartPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
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
