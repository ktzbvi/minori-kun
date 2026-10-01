<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Leaf } from 'lucide-vue-next'
import { queryClient } from '@/lib/query'
import { currentSessionQuery } from '@/services/auth/auth.query'
import { producerRegistrationStatusQuery } from '@/services/registration/registration.query'
import { recoverCompletedProducerRegistration } from '@/services/registration/registration.mutation'

const router = useRouter()

async function routeToRegistrationState() {
  try {
    const registration = await queryClient.fetchQuery({ ...producerRegistrationStatusQuery, staleTime: 0 })

    if (registration.state === 'pending') return await router.replace({ name: 'register-verify' })
    if (registration.state === 'verified') return await router.replace({ name: 'register-details' })
    if (registration.state === 'expired') {
      return await router.replace({ name: 'register', query: { recovery: 'expired' } })
    }
    if (registration.state === 'consumed') {
      try {
        const session = await recoverCompletedProducerRegistration()
        queryClient.setQueryData(currentSessionQuery.queryKey, session)
        return await router.replace({ name: session.producer?.eligible_to_sell ? 'dashboard' : 'onboarding' })
      } catch {
        return await router.replace({ name: 'login', query: { recovery: 'registration-complete' } })
      }
    }
  } catch {
    return await router.replace({ name: 'login' })
  }

  return await router.replace({ name: 'register' })
}

onMounted(async () => {
  try {
    const session = await queryClient.fetchQuery({ ...currentSessionQuery, staleTime: 0 })
    if (session.role !== 'producer') {
      await router.replace({ name: 'login' })
      return
    }
    await router.replace({ name: session.producer?.eligible_to_sell ? 'dashboard' : 'onboarding' })
  } catch {
    await routeToRegistrationState()
  }
})
</script>

<template>
  <main class="grid min-h-svh place-items-center bg-[#f3f7f4] px-6 text-[#1d2b24]">
    <section class="grid justify-items-center gap-4 text-center" role="status" aria-live="polite">
      <span class="grid size-16 place-items-center rounded-full bg-[#e8f5ed] text-white" aria-hidden="true">
        <Leaf class="size-11 rounded-full bg-[#1b7a49] p-2.5" :stroke-width="2.5" />
      </span>
      <div>
        <p class="text-2xl font-extrabold text-[#1b7a49]">みのりくん</p>
        <p class="mt-2 text-sm font-medium text-[#687b70]">登録状況を確認しています...</p>
      </div>
    </section>
  </main>
</template>
