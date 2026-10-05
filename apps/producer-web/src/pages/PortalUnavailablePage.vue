<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { Clock3 } from 'lucide-vue-next'

defineProps<{ title: string }>()
</script>

<template>
  <section>
    <h1 class="text-2xl leading-tight font-extrabold text-[#17241d] min-[761px]:text-3xl lg:text-4xl">
      {{ title }}
    </h1>

    <div class="mt-6 grid min-h-56 lg:mt-8 lg:min-h-72 place-items-center rounded-2xl border border-[#d6e2da] bg-white p-4 lg:p-8 text-center shadow-sm">
      <div class="max-w-lg">
        <span class="mx-auto grid size-12 place-items-center rounded-full bg-[#e8f5ed] text-[#16804d]" aria-hidden="true">
          <Clock3 class="size-6" :stroke-width="2.5" />
        </span>
        <h2 class="mt-4 text-lg lg:mt-5 lg:text-xl font-extrabold text-[#17241d]">この画面は現在準備中です。</h2>
        <p class="mt-2 text-base leading-relaxed text-[#68766e]">
          利用可能になるまで、ダッシュボードまたは商品管理をご利用ください。
        </p>
        <RouterLink
          to="/dashboard"
          class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#24884f] px-6 text-base font-bold text-white hover:bg-[#176b3e] focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#237f4b]"
        >
          ダッシュボードへ戻る
        </RouterLink>
      </div>
    </div>
  </section>
</template>
