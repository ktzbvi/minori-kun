<script setup lang="ts">
import { UiCard } from '@minorikun/ui'
import AdminAppLayout from '@/layouts/AdminAppLayout.vue'

type SummaryItem = { label: string; value: string; toneClass?: string }
type SummaryGroup = { title: string; destination: string; items: SummaryItem[] }

const summaryGroups: SummaryGroup[] = [
  {
    title: '生産者状況',
    destination: '生産者管理',
    items: [
      { label: 'PAY.JP申請未完了', value: '—', toneClass: 'bg-[#d8951d]' },
      { label: '審査中', value: '—', toneClass: 'bg-[#d8951d]' },
      { label: '販売資格喪失', value: '—', toneClass: 'bg-[#c5503c]' },
    ],
  },
  {
    title: '商品・注文注意',
    destination: '商品・注文管理',
    items: [
      { label: '問題商品', value: '—', toneClass: 'bg-[#d8951d]' },
      { label: '返金・チャージバック', value: '—', toneClass: 'bg-[#c5503c]' },
      { label: '管理者対応が必要な注文', value: '—', toneClass: 'bg-[#d8951d]' },
    ],
  },
  {
    title: '売上・会社手数料',
    destination: '売上・会社手数料',
    items: [
      { label: '当期総売上', value: '—' },
      { label: '会社手数料・受取済み', value: '—' },
      { label: '会社手数料・未受取', value: '—' },
    ],
  },
  {
    title: '振込状況',
    destination: '振込管理',
    items: [
      { label: '振込予定', value: '—' },
      { label: '振込済み', value: '—' },
      { label: '失敗', value: '—', toneClass: 'bg-[#c5503c]' },
      { label: '保留', value: '—', toneClass: 'bg-[#d8951d]' },
    ],
  },
]

const securityAlerts = ['ログイン失敗', '不正アクセス', 'PAY.JPコールバック', 'システムエラー']
</script>

<template>
  <AdminAppLayout>
    <main
      class="mx-auto w-full max-w-[1280px] px-6 pt-10 pb-16 max-[680px]:px-[18px] max-[680px]:pt-7 max-[680px]:pb-12"
    >
      <header class="mb-7 flex items-start justify-between gap-6 max-[680px]:grid">
        <div>
          <p class="mb-1.5 text-xs font-extrabold text-[var(--color-primary)]">管理ポータル</p>
          <h1 class="m-0 text-[clamp(28px,3vw,38px)] leading-[1.3] font-bold">
            管理ダッシュボード
          </h1>
          <p class="mt-2.5 mb-0 text-sm text-[var(--color-muted)]">
            対応が必要な状況と運用サマリーを確認できます。
          </p>
        </div>
        <span
          class="inline-flex min-h-9 items-center gap-2 whitespace-nowrap rounded-full border border-[#d9e3dc] bg-white px-3.5 text-xs font-bold text-[var(--color-muted)] max-[680px]:w-fit"
          ><span class="size-[7px] rounded-full bg-[#c58b18]" aria-hidden="true" />集計待ち</span
        >
      </header>

      <section class="grid grid-cols-2 gap-5 max-[680px]:grid-cols-1" aria-label="運用サマリー">
        <UiCard v-for="group in summaryGroups" :key="group.title" class="!overflow-hidden !p-0">
          <div
            class="flex min-h-[68px] items-center justify-between gap-4 border-b border-[#e2e9e4] px-[22px] py-[18px]"
          >
            <h2 class="m-0 text-[17px] font-bold">{{ group.title }}</h2>
            <span class="text-xs font-bold text-[var(--color-primary)]">{{
              group.destination
            }}</span>
          </div>
          <dl class="m-0 px-[22px] pt-2 pb-3.5">
            <div
              v-for="item in group.items"
              :key="item.label"
              class="flex min-h-[50px] items-center justify-between gap-5 border-b border-[#edf1ee] last:border-b-0"
            >
              <dt class="flex items-center gap-2.5 text-[13px] text-[#526159]">
                <span
                  class="size-2 shrink-0 rounded-full"
                  :class="item.toneClass ?? 'bg-[#4f8c6b]'"
                  aria-hidden="true"
                />{{ item.label }}
              </dt>
              <dd class="m-0 text-xl font-extrabold">{{ item.value }}</dd>
            </div>
          </dl>
        </UiCard>
      </section>

      <UiCard class="mt-5 !overflow-hidden !p-0">
        <div
          class="flex min-h-[68px] items-center justify-between gap-4 border-b border-[#e2e9e4] px-[22px] py-[18px]"
        >
          <div>
            <h2 class="m-0 text-[17px] font-bold">重要な通知</h2>
            <p class="mt-1.5 mb-0 text-xs text-[var(--color-muted)]">
              セキュリティ・決済連携・システム状態
            </p>
          </div>
          <span class="text-xs font-bold text-[var(--color-primary)]">監査ログ</span>
        </div>
        <div
          class="grid grid-cols-4 gap-3 px-[22px] py-5 max-[1080px]:grid-cols-2 max-[680px]:grid-cols-1"
        >
          <div
            v-for="alert in securityAlerts"
            :key="alert"
            class="flex min-h-[82px] items-center gap-3 rounded-lg bg-[#f8faf8] px-[18px] py-3.5"
          >
            <span
              class="grid size-[30px] shrink-0 place-items-center rounded-full bg-[#f8ead0] text-[13px] font-black text-[#9b650c]"
              aria-hidden="true"
              >!</span
            >
            <div>
              <strong class="text-xs">{{ alert }}</strong>
              <p class="mt-1 mb-0 text-lg font-extrabold text-[var(--color-muted)]">—</p>
            </div>
          </div>
        </div>
      </UiCard>
    </main>
  </AdminAppLayout>
</template>
