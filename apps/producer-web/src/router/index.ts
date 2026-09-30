import { createRouter, createWebHistory } from 'vue-router'
import { queryClient } from '@/lib/query'
import { currentSessionQuery } from '@/services/auth/auth.query'
import { producerRegistrationStatusQuery } from '@/services/registration/registration.query'
import { recoverCompletedProducerRegistration } from '@/services/registration/registration.mutation'
import HomePage from '@/pages/HomePage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import OnboardingPage from '@/pages/OnboardingPage.vue'
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
    { path: '/', name: 'home', component: HomePage },
  ],
})

router.beforeEach(async (to) => {
  if (to.name === 'register' || to.name === 'register-verify' || to.name === 'register-details') {
    try {
      const session = await queryClient.fetchQuery({ ...currentSessionQuery, staleTime: 0 })
      if (session.role === 'producer') {
        return { name: session.producer?.eligible_to_sell ? 'home' : 'onboarding' }
      }
      return { name: 'login' }
    } catch {
      // Registration is a public entry point. A missing or temporarily unavailable
      // session must not bounce a new applicant back to login.
    }

    try {
      const registration = await queryClient.fetchQuery({ ...producerRegistrationStatusQuery, staleTime: 0 })
      if (registration.state === 'pending') {
        return to.name === 'register-verify' ? true : { name: 'register-verify' }
      }
      if (registration.state === 'verified') {
        return to.name === 'register-details' ? true : { name: 'register-details' }
      }
      if (registration.state === 'consumed') {
        try {
          const session = await recoverCompletedProducerRegistration()
          queryClient.setQueryData(currentSessionQuery.queryKey, session)
          return { name: session.producer?.eligible_to_sell ? 'home' : 'onboarding' }
        } catch {
          return { name: 'login', query: { recovery: 'registration-complete' } }
        }
      }
      if (to.name !== 'register') {
        return { name: 'register', query: { recovery: registration.state === 'expired' ? 'expired' : 'missing' } }
      }
      return true
    } catch {
      // Registration pages render the query error with Retry; a network error
      // never becomes an assumed absent registration attempt.
      return true
    }
  }

  if (to.meta.public) return true

  try {
    const session = await queryClient.fetchQuery({ ...currentSessionQuery, staleTime: 0 })
    if (session.role !== 'producer') return { name: 'login' }
    const eligible = session.producer?.eligible_to_sell === true
    if (to.name === 'onboarding' && eligible) return { name: 'home' }
    if (to.name === 'home' && !eligible) return { name: 'onboarding' }
    return true
  } catch {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
})
