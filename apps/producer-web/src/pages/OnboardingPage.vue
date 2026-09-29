<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query'
import { UiButton, UiCard } from '@minorikun/ui'
import { producerOnboardingQuery } from '@/services/registration/registration.query'

const onboardingQuery = useQuery(producerOnboardingQuery)
</script>

<template>
  <main class="min-h-svh bg-[#f3f7f4] px-5 py-10 min-[761px]:grid min-[761px]:place-items-center min-[761px]:px-10">
    <UiCard class="mx-auto w-full max-w-[760px] rounded-2xl border border-[#cfdfd5] bg-white p-6 shadow-sm min-[761px]:p-10">
      <header>
        <p class="text-sm font-semibold text-[#237b4d]">生産者ポータル</p>
        <h1 class="mt-2 text-2xl font-bold text-[#1e2923]">販売開始の準備</h1>
      </header>

      <div v-if="onboardingQuery.isPending.value" class="mt-7 rounded-xl border border-[#d9e5dd] bg-[#f8fbf9] p-5 text-sm text-[#687b70]" role="status">
        販売資格の状態を確認しています…
      </div>
      <div v-else-if="onboardingQuery.isError.value" class="mt-7 rounded-xl border border-[#e4c9c3] bg-white p-5">
        <p class="text-sm leading-relaxed text-[#7b3329]" role="alert">販売資格の状態を取得できませんでした。通信状態を確認して再試行してください。</p>
        <UiButton class="mt-4" variant="outline" :disabled="onboardingQuery.isFetching.value" @click="onboardingQuery.refetch()">
          {{ onboardingQuery.isFetching.value ? '確認中…' : '再試行' }}
        </UiButton>
      </div>
      <div v-else-if="onboardingQuery.data.value?.state === 'application_required'" class="mt-7 rounded-xl border border-[#d9e5dd] bg-[#f8fbf9] p-5 min-[761px]:p-7">
        <p class="text-base font-bold text-[#25332b]">PAY.JPの申請が必要です。</p>
        <p v-if="!onboardingQuery.data.value.application_available" class="mt-3 text-sm leading-relaxed text-[#687b70]" role="status">
          申請手続きは現在利用できません。時間をおいて、もう一度ご確認ください。
        </p>
        <p v-else class="mt-3 text-sm leading-relaxed text-[#687b70]" role="status">
          販売を開始するには申請と審査が必要です。申請状況が更新されるまで、販売機能は利用できません。
        </p>
      </div>
      <div v-else class="mt-7 rounded-xl border border-[#d9e5dd] bg-[#f8fbf9] p-5" role="status">
        <p class="text-sm leading-relaxed text-[#687b70]">販売資格の状態を更新しています。ページを再読み込みしてください。</p>
      </div>
    </UiCard>
  </main>
</template>
