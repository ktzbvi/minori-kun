import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { logoutBuyer } from '@/services/auth/auth.mutation'
import { queryClient } from '@/lib/query'
import { buyerAuthKeys } from '@/services/auth/auth.key'
import { buyerCartKeys } from '@/services/cart/cart.key'
import type { BuyerAccountProfile } from '@/types/account'

export function useBuyerMyPage() {
  const router = useRouter()
  const profile = ref<BuyerAccountProfile | null>(null)
  const logoutOpen = ref(false)
  const loggingOut = ref(false)
  onMounted(async () => {
    try {
      profile.value = await getBuyerAccountProfile()
    } catch {
      await router.replace({ name: 'login', query: { redirect: '/my-page' } })
    }
  })
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function openCart() {
    void router.push({ name: 'cart' })
  }
  async function confirmLogout() {
    loggingOut.value = true

    try {
      await logoutBuyer()
      await queryClient.cancelQueries({ queryKey: buyerCartKeys.all() })
      queryClient.removeQueries({ queryKey: buyerCartKeys.all() })
      queryClient.setQueryData(buyerAuthKeys.currentSession(), null)
      await router.replace({ name: 'home' })
      toast.success('ログアウトしました。')
    } catch {
      toast.error('ログアウトできませんでした。時間をおいてからもう一度お試しください。')
      return
    } finally {
      loggingOut.value = false
    }

    logoutOpen.value = false
  }
  return { router, profile, logoutOpen, loggingOut, openSearch, openCart, confirmLogout }
}
