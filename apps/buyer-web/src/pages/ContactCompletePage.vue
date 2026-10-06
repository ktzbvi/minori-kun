<script setup lang="ts">
import { Check, ChevronLeft } from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { UiButton } from '@minorikun/ui'
import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'

const route = useRoute()
const router = useRouter()
const referenceNumber = computed(() => String(route.query.reference ?? ''))
const isProducerInquiry = computed(() => route.query.type === 'producer')

function returnToMyPage() {
  void router.replace({ name: 'my-page' })
}
</script>

<template>
  <main class="min-h-screen bg-[#e4ebe6] text-[#26362c] sm:grid sm:place-items-center sm:p-6">
    <section
      class="relative flex h-dvh w-full max-w-[375px] flex-col overflow-hidden bg-[#f8faf6] sm:h-[728px] sm:shadow-sm"
    >
      <header
        class="flex h-[65px] shrink-0 items-center gap-2 border-b border-[#e3e9e3] bg-white px-3"
      >
        <button
          class="grid size-9 place-items-center border-0 bg-transparent text-[#237d4a]"
          type="button"
          aria-label="Back to My Page"
          @click="returnToMyPage"
        >
          <ChevronLeft :size="21" :stroke-width="2.5" aria-hidden="true" />
        </button>
        <h1 class="m-0 text-[16px] font-bold text-[#237d4a]">お問い合わせ完了</h1>
      </header>

      <div class="flex min-h-0 flex-1 flex-col items-center px-5 pt-11 pb-[76px] text-center">
        <div class="grid size-[72px] place-items-center rounded-full bg-[#eaf6ee] text-[#237d4a]">
          <Check :size="35" :stroke-width="1.5" aria-hidden="true" />
        </div>
        <h2 class="mt-6 mb-2 text-[17px] font-bold text-[#237d4a]">お問い合わせを受け付けました</h2>
        <p class="m-0 text-[11px] leading-5 text-[#718075]">
          内容を確認のうえ、担当者よりご連絡いたします。
        </p>
        <p v-if="referenceNumber && !isProducerInquiry" class="mt-3 text-[10px] text-[#718075]">
          受付番号: {{ referenceNumber }}
        </p>
        <UiButton
          class="mt-5 !min-h-[35px] !w-full !rounded-[4px] !border-[#237d4a] !bg-white !text-[12px] !text-[#237d4a]"
          type="button"
          variant="outline"
          @click="returnToMyPage"
        >
          マイページへ戻る
        </UiButton>
      </div>

      <BuyerBottomNavigation active="profile" />
    </section>
  </main>
</template>
