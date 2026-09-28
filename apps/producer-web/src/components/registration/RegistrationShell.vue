<script setup lang="ts">
withDefaults(defineProps<{ compactMobile?: boolean }>(), { compactMobile: false })
</script>

<template>
  <main class="relative isolate min-h-svh overflow-hidden bg-linear-[145deg,#e4f3ed_0%,#f8f8ed_68%,#f4f7ec_100%] min-[761px]:grid min-[761px]:grid-cols-[35%_minmax(0,1fr)] min-[761px]:overflow-visible min-[761px]:bg-[#f3f7f4] min-[761px]:bg-none">
    <aside class="hidden items-center bg-[#14382a] px-[clamp(32px,3.8vw,96px)] py-12 text-[#eaf0dd] min-[761px]:flex min-[761px]:min-h-svh" aria-label="生産者ポータル">
      <div class="w-full">
        <p class="mb-1 text-[32px] font-extrabold tracking-[.04em] italic min-[761px]:mb-5 min-[761px]:text-[clamp(36px,3vw,52px)]">みのりくん</p>
        <p class="text-[17px] font-semibold text-[#a8c2ae] min-[761px]:text-[clamp(20px,1.55vw,28px)]">生産者ポータル</p>
      </div>
    </aside>

    <div class="absolute right-[10%] bottom-[13%] -z-10 size-28 rounded-full bg-[#f4eac1]/75 min-[761px]:hidden" aria-hidden="true"></div>
    <svg class="absolute inset-x-0 bottom-0 -z-10 h-[245px] w-full min-[761px]:hidden" viewBox="0 0 780 245" preserveAspectRatio="none" aria-hidden="true">
      <path d="M0 62c125-73 213-43 324 17 104 56 177 35 282-5 74-28 123-31 174-22v193H0Z" fill="#d8eee4" />
      <path d="M0 126c120-65 205-32 311 22 105 54 181 31 286-9 73-28 126-33 183-22v128H0Z" fill="#b8ddca" />
      <path d="M0 191c112-73 210-58 326-4 106 49 184 42 286 3 72-28 122-29 168-21v76H0Z" fill="#8fc9ab" />
      <g fill="none" stroke="#5fa67f" stroke-linecap="round" stroke-width="3" opacity=".55">
        <path d="M-20 245c132-102 245-120 370-139M270 245c121-86 254-108 427-126M500 245c85-55 175-75 290-82" />
        <path d="M95 224v-53l47-42 47 42v53m-78 0v-55h62v55m-86 0h111" />
      </g>
      <g fill="#ead47c" opacity=".9"><circle cx="304" cy="114" r="6"/><circle cx="326" cy="105" r="4"/><circle cx="352" cy="118" r="5"/></g>
    </svg>

    <section
      class="relative z-10 grid min-w-0 justify-items-center px-7 pb-[270px] min-[761px]:min-h-svh min-[761px]:place-items-center min-[761px]:px-[clamp(40px,7.6vw,160px)] min-[761px]:py-12"
      :class="compactMobile ? 'pt-12' : 'pt-[clamp(150px,19vh,320px)]'"
    >
      <slot />
    </section>
  </main>
</template>
