<template>
  <main class="flex min-h-svh flex-col bg-[#f3f7f4] min-[761px]:grid min-[761px]:grid-cols-[35%_minmax(0,1fr)]">
    <aside class="flex min-h-36 items-center bg-[#14382a] px-6 py-7 text-[#eaf0dd] min-[761px]:min-h-svh min-[761px]:px-[clamp(32px,3.8vw,96px)] min-[761px]:py-12" aria-label="生産者ポータル">
      <div class="w-full">
        <p class="mb-1 text-[32px] font-extrabold tracking-[.04em] italic min-[761px]:mb-5 min-[761px]:text-[clamp(36px,3vw,52px)]">みのりくん</p>
        <p class="text-[17px] font-semibold text-[#a8c2ae] min-[761px]:text-[clamp(20px,1.55vw,28px)]">生産者ポータル</p>
      </div>
    </aside>

    <section class="grid min-w-0 flex-1 items-start justify-items-center px-5 py-10 min-[761px]:min-h-svh min-[761px]:place-items-center min-[761px]:px-[clamp(40px,7.6vw,160px)] min-[761px]:py-12">
      <slot />
    </section>
  </main>
</template>
