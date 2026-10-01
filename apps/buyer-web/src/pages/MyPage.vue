<script setup lang="ts">
import {
  LogOut,
  Mail,
  PackageOpen,
  Pencil,
  Search,
  ShoppingCart,
  ChevronRight,
  KeyRound,
} from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { getBuyerAccountProfile } from '@/services/account/account.api'
import { logoutBuyer } from '@/services/auth/auth.mutation'
import { queryClient } from '@/lib/query'
import type { BuyerAccountProfile } from '@/types/account'

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

function showInPreparation() {
  toast.warning('この画面は準備中です。')
}

async function confirmLogout() {
  loggingOut.value = true

  try {
    await logoutBuyer()
    queryClient.removeQueries({ queryKey: ['current-session'] })
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
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] shrink-0 items-center justify-between border-b border-[#e3e9e3] bg-white px-4"
      >
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">
          &#12510;&#12452;&#12506;&#12540;&#12472;
        </h1>
        <div class="flex gap-2">
          <button
            class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Search"
            @click="openSearch"
          >
            <Search :size="21" />
          </button>
          <button
            class="grid size-9 place-items-center border-0 bg-transparent text-[#627469]"
            type="button"
            aria-label="Cart"
            @click="openCart"
          >
            <ShoppingCart :size="21" />
          </button>
        </div>
      </header>

      <div class="min-h-0 flex-1 overflow-y-auto px-3 pt-4 pb-[76px]">
        <section class="rounded-[8px] border border-[#dce5dc] bg-white px-3 py-2.5">
          <div>
            <p class="m-0 text-[13px] font-bold">{{ profile?.name || '&#35501;&#21517;' }}</p>
            <p class="m-0 mt-0.5 text-[9px] text-[#718075]">
              &#20250;&#21729;&#30058;&#21495;: {{ profile?.member_id || '-' }}
            </p>
          </div>
        </section>

        <p class="mb-1 mt-4 text-[10px] text-[#718075]">&#27880;&#25991;&#38306;&#36899;</p>
        <div class="menu-group">
          <button class="menu-row" type="button" @click="showInPreparation">
            <PackageOpen :size="15" />
            <span>&#27880;&#25991;&#23653;&#27508;</span>
            <small>&#28310;&#20633;&#20013;</small>
            <ChevronRight :size="16" />
          </button>
        </div>

        <p class="mb-1 mt-4 text-[10px] text-[#718075]">
          &#12450;&#12459;&#12454;&#12531;&#12488;&#35373;&#23450;
        </p>
        <div class="menu-group">
          <button class="menu-row" type="button" @click="router.push({ name: 'member-info' })">
            <Pencil :size="15" />
            <span>&#20250;&#21729;&#24773;&#22577;&#12398;&#22793;&#26356;</span>
            <ChevronRight :size="16" />
          </button>
          <button class="menu-row" type="button" @click="router.push({ name: 'password-change' })">
            <KeyRound :size="15" />
            <span>&#12497;&#12473;&#12527;&#12540;&#12489;&#12398;&#22793;&#26356;</span>
            <ChevronRight :size="16" />
          </button>
        </div>

        <p class="mb-1 mt-4 text-[10px] text-[#718075]">&#12381;&#12398;&#20182;</p>
        <div class="menu-group">
          <button class="menu-row" type="button" @click="router.push({ name: 'contact' })">
            <Mail :size="15" />
            <span>&#12362;&#21839;&#12356;&#21512;&#12431;&#12379;</span>
            <ChevronRight :size="16" />
          </button>
          <button class="menu-row" type="button" @click="logoutOpen = true">
            <LogOut :size="15" />
            <span>&#12525;&#12464;&#12450;&#12454;&#12488;</span>
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>

      <BuyerBottomNavigation active="profile" />

      <div
        v-if="logoutOpen"
        class="absolute inset-0 z-10 grid place-items-center bg-black/30 px-7"
        role="presentation"
      >
        <section
          class="w-full rounded-[9px] bg-white p-4 shadow-lg"
          role="dialog"
          aria-modal="true"
          aria-labelledby="logout-title"
        >
          <h2 id="logout-title" class="m-0 text-center text-[14px] font-bold">
            &#12525;&#12464;&#12450;&#12454;&#12488;&#12375;&#12414;&#12377;&#12363;&#65311;
          </h2>
          <p class="mt-2 mb-4 text-center text-[10px] text-[#718075]">
            &#29694;&#22312;&#12398;&#12450;&#12459;&#12454;&#12531;&#12488;&#12363;&#12425;&#12525;&#12464;&#12450;&#12454;&#12488;&#12375;&#12414;&#12377;&#12290;
          </p>
          <div class="grid grid-cols-2 gap-2">
            <button
              class="modal-button border-[#dce5dc] bg-white text-[#4d6155]"
              type="button"
              :disabled="loggingOut"
              @click="logoutOpen = false"
            >
              &#12461;&#12515;&#12531;&#12475;&#12523;
            </button>
            <button
              class="modal-button border-[#d94444] bg-[#d94444] text-white"
              type="button"
              :disabled="loggingOut"
              @click="confirmLogout"
            >
              &#12525;&#12464;&#12450;&#12454;&#12488;
            </button>
          </div>
        </section>
      </div>
    </section>
  </main>
</template>

<style scoped>
.menu-group {
  overflow: hidden;
  border: 1px solid #dce5dc;
  border-radius: 7px;
}

.menu-row {
  display: flex;
  width: 100%;
  min-height: 41px;
  align-items: center;
  gap: 8px;
  border: 0;
  border-bottom: 1px solid #dce5dc;
  background: #fff;
  padding: 0 10px;
  text-align: left;
  font-size: 12px;
  color: #33443a;
}

.menu-row:last-child {
  border-bottom: 0;
}

.menu-row > span {
  flex: 1;
}

.menu-row small {
  color: #8c9a90;
  font-size: 9px;
}

.modal-button {
  min-height: 32px;
  border-width: 1px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 700;
}
</style>
