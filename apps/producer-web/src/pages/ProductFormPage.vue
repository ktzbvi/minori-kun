<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { z } from 'zod'
import {
  ArrowLeft,
  ChevronLeft,
  ChevronRight,
  ImagePlus,
  LoaderCircle,
  X,
} from 'lucide-vue-next'
import {
  toast,
  UiButton,
  UiCard,
  UiFormLabel,
  UiFormMessage,
  UiInput,
  UiSelect,
  UiSelectTrigger,
  UiSelectContent,
  UiSelectItem,
  UiSelectValue,
  UiTextarea,
} from '@minorikun/ui'
import { useProducerProductOptionsQuery, useProducerProductQuery } from '@/services/products/product.query'
import { useSaveProducerProductMutation } from '@/services/products/product.mutation'
import type { ProducerProductDetail, ProducerProductPublicationState } from '@/types/product'

interface FormImage {
  token: string
  url: string
  file?: File
}

const integerText = z.string().regex(/^\d+$/, '0以上の整数で入力してください。')
const formSchema = z.object({
  name: z.string().trim().min(1, '商品名を入力してください。').max(255, '255文字以内で入力してください。'),
  categoryId: z.string().min(1, 'カテゴリを選択してください。'),
  description: z.string().trim().min(1, '商品説明を入力してください。').max(10000, '10,000文字以内で入力してください。'),
  priceYen: integerText,
  stockQuantity: integerText,
  discountPercent: z.string().refine(
    (value) => value === '' || (/^\d+(\.\d{1,2})?$/.test(value) && Number(value) <= 100),
    '0〜100の範囲で入力してください。',
  ),
  deliveryFeeHonshuYen: integerText,
  deliveryFeeHokkaidoYen: integerText,
  deliveryFeeOkinawaYen: integerText,
})

const route = useRoute()
const router = useRouter()
const productId = computed(() => typeof route.params.id === 'string' ? route.params.id : '')
const isEdit = computed(() => productId.value !== '')
const optionsQuery = useProducerProductOptionsQuery()
const productQuery = useProducerProductQuery(productId, isEdit)
const saveMutation = useSaveProducerProductMutation()

const name = ref('')
const categoryId = ref('')
const description = ref('')
const priceYen = ref('0')
const stockQuantity = ref('0')
const discountPercent = ref('')
const deliveryFeeHonshuYen = ref('0')
const deliveryFeeHokkaidoYen = ref('0')
const deliveryFeeOkinawaYen = ref('0')
const lockVersion = ref<number | null>(null)
const currentPublicationState = ref<ProducerProductPublicationState>('draft')
const images = ref<FormImage[]>([])
const errors = ref<Record<string, string>>({})
const formError = ref('')
const hydratedId = ref('')

const pageTitle = computed(() => isEdit.value ? '商品を編集' : '商品を登録')
const isInitialLoading = computed(() => optionsQuery.isPending.value || (isEdit.value && productQuery.isPending.value))
const initialLoadFailed = computed(() => optionsQuery.isError.value || (isEdit.value && productQuery.isError.value))

function hydrate(product: ProducerProductDetail) {
  if (hydratedId.value === product.id) return
  hydratedId.value = product.id
  name.value = product.name
  categoryId.value = product.category_id
  description.value = product.description
  priceYen.value = String(product.price_yen)
  stockQuantity.value = String(product.stock_quantity)
  discountPercent.value = product.discount_bps === 0 ? '' : String(product.discount_bps / 100)
  deliveryFeeHonshuYen.value = String(product.delivery_fee_honshu_yen)
  deliveryFeeHokkaidoYen.value = String(product.delivery_fee_hokkaido_yen)
  deliveryFeeOkinawaYen.value = String(product.delivery_fee_okinawa_yen)
  lockVersion.value = product.lock_version
  currentPublicationState.value = product.publication_state
  images.value = product.images.map((image) => ({ token: `existing:${image.id}`, url: image.url }))
}

watch(() => productQuery.data.value, (product) => {
  if (product) hydrate(product)
}, { immediate: true })

function addImages(event: Event) {
  const input = event.target as HTMLInputElement
  const files = Array.from(input.files ?? [])
  formError.value = ''

  for (const file of files) {
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      formError.value = `${file.name} はJPEG、PNG、WebP形式ではありません。`
      continue
    }
    if (file.size > 10 * 1024 * 1024) {
      formError.value = `${file.name} は10MBを超えています。`
      continue
    }
    images.value.push({ token: `new:${crypto.randomUUID()}`, url: URL.createObjectURL(file), file })
  }
  input.value = ''
}

function removeImage(index: number) {
  const [removed] = images.value.splice(index, 1)
  if (removed?.file) URL.revokeObjectURL(removed.url)
}

function moveImage(index: number, offset: -1 | 1) {
  const nextIndex = index + offset
  if (nextIndex < 0 || nextIndex >= images.value.length) return
  const next = [...images.value]
  ;[next[index], next[nextIndex]] = [next[nextIndex]!, next[index]!]
  images.value = next
}

function previewPrice(fee: string) {
  const base = Number(priceYen.value) || 0
  const deliveryFee = Number(fee) || 0
  const discountBps = Math.round((Number(discountPercent.value) || 0) * 100)
  return Math.floor((base + deliveryFee) * (10000 - discountBps) / 10000).toLocaleString('ja-JP')
}

function mapServerErrors(serverErrors: Record<string, string[] | string>) {
  const fieldMap: Record<string, string> = {
    name: 'name', category_id: 'categoryId', description: 'description', price_yen: 'priceYen',
    stock_quantity: 'stockQuantity', discount_percent: 'discountPercent',
    delivery_fee_honshu_yen: 'deliveryFeeHonshuYen', delivery_fee_hokkaido_yen: 'deliveryFeeHokkaidoYen',
    delivery_fee_okinawa_yen: 'deliveryFeeOkinawaYen', image_order: 'images', new_images: 'images',
  }
  for (const [key, messages] of Object.entries(serverErrors)) {
    const normalized = key.replace(/\.\d+$/, '')
    const target = fieldMap[normalized]
    if (target) errors.value[target] = Array.isArray(messages) ? (messages[0] ?? '') : messages
  }
}

async function submit(publicationState: ProducerProductPublicationState) {
  errors.value = {}
  formError.value = ''
  const parsed = formSchema.safeParse({
    name: name.value,
    categoryId: categoryId.value,
    description: description.value,
    priceYen: priceYen.value,
    stockQuantity: stockQuantity.value,
    discountPercent: discountPercent.value,
    deliveryFeeHonshuYen: deliveryFeeHonshuYen.value,
    deliveryFeeHokkaidoYen: deliveryFeeHokkaidoYen.value,
    deliveryFeeOkinawaYen: deliveryFeeOkinawaYen.value,
  })
  if (!parsed.success) {
    for (const issue of parsed.error.issues) errors.value[String(issue.path[0])] ??= issue.message
  }
  if (images.value.length === 0) errors.value.images = '商品画像を1枚以上追加してください。'
  if (!parsed.success || images.value.length === 0) {
    formError.value = '入力内容を確認してください。'
    return
  }

  const formData = new FormData()
  formData.set('name', parsed.data.name)
  formData.set('category_id', parsed.data.categoryId)
  formData.set('description', parsed.data.description)
  formData.set('price_yen', parsed.data.priceYen)
  formData.set('stock_quantity', parsed.data.stockQuantity)
  formData.set('discount_percent', parsed.data.discountPercent)
  formData.set('delivery_fee_honshu_yen', parsed.data.deliveryFeeHonshuYen)
  formData.set('delivery_fee_hokkaido_yen', parsed.data.deliveryFeeHokkaidoYen)
  formData.set('delivery_fee_okinawa_yen', parsed.data.deliveryFeeOkinawaYen)
  formData.set('publication_state', publicationState)
  if (lockVersion.value !== null) formData.set('lock_version', String(lockVersion.value))

  let newIndex = 0
  const imageOrder = images.value.map((image) => {
    if (!image.file) return image.token
    formData.append(`new_images[${newIndex}]`, image.file)
    return `new:${newIndex++}`
  })
  formData.set('image_order', JSON.stringify(imageOrder))

  try {
    const result = await saveMutation.mutateAsync({ id: isEdit.value ? productId.value : undefined, formData })
    if (result.rejectedImages.length > 0) {
      toast.warning(`${result.rejectedImages.length}枚の画像を除外して商品を保存しました。`)
    } else {
      toast.success(publicationState === 'published' ? '商品を公開しました。' : '商品を保存しました。')
    }
    await router.push({ name: 'products' })
  } catch (error) {
    if (axios.isAxiosError(error)) {
      const responseErrors = error.response?.data?.errors
      if (responseErrors && typeof responseErrors === 'object') mapServerErrors(responseErrors)
      formError.value = error.response?.status === 409
        ? 'ほかの更新が反映されています。再読み込みしてからやり直してください。'
        : (error.response?.data?.message || '商品を保存できませんでした。')
    } else {
      formError.value = '商品を保存できませんでした。'
    }
  }
}

async function retryInitialLoad() {
  await Promise.all([optionsQuery.refetch(), isEdit.value ? productQuery.refetch() : Promise.resolve()])
}

onBeforeUnmount(() => {
  images.value.forEach((image) => { if (image.file) URL.revokeObjectURL(image.url) })
})
</script>

<template>
  <div class="mx-auto w-full max-w-[1180px]">
    <RouterLink to="/products" class="inline-flex items-center gap-1 text-sm font-bold text-[#147b49] hover:underline">
      <ArrowLeft class="size-4" aria-hidden="true" /> 商品一覧へ
    </RouterLink>
    <h1 class="mt-3 text-xl sm:text-3xl font-extrabold tracking-tight text-[#1d2b24]">{{ pageTitle }}</h1>

    <div v-if="isInitialLoading" class="grid min-h-72 place-items-center" role="status">
      <LoaderCircle class="size-8 animate-spin text-[#147b49]" aria-hidden="true" />
      <span class="sr-only">読み込み中</span>
    </div>

    <UiCard v-else-if="initialLoadFailed" class="mt-6 text-center">
      <p class="font-bold">商品情報を読み込めませんでした。</p>
      <UiButton class="mt-4" @click="retryInitialLoad">再試行</UiButton>
    </UiCard>

    <form v-else class="mt-6" novalidate @submit.prevent="submit('published')">
      <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1.05fr)_minmax(360px,.95fr)]">
        <div class="grid gap-5">
          <UiCard class="p-5 sm:p-6">
            <h2 class="text-lg sm:text-xl font-extrabold">基本情報</h2>
            <div class="mt-5 grid gap-5">
              <div>
                <UiFormLabel for="product-name" class="font-bold">商品名 <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></UiFormLabel>
                <UiInput id="product-name" v-model="name" class="mt-2 h-12" placeholder="商品名を入力" :aria-invalid="!!errors.name" />
                <UiFormMessage v-if="errors.name" class="mt-1" role="alert">{{ errors.name }}</UiFormMessage>
              </div>
              <div>
                <UiFormLabel for="product-category" class="font-bold">カテゴリ <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></UiFormLabel>
                <UiSelect v-model="categoryId">
                  <UiSelectTrigger id="product-category" class="mt-2 h-12" :aria-invalid="!!errors.categoryId"><UiSelectValue placeholder="カテゴリを選択" /></UiSelectTrigger>
                  <UiSelectContent>
                    <UiSelectItem v-for="category in optionsQuery.data.value?.categories ?? []" :key="category.id" :value="category.id">{{ category.name }}</UiSelectItem>
                  </UiSelectContent>
                </UiSelect>
                <UiFormMessage v-if="errors.categoryId" class="mt-1" role="alert">{{ errors.categoryId }}</UiFormMessage>
              </div>
              <div>
                <UiFormLabel for="product-description" class="font-bold">商品説明 <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></UiFormLabel>
                <UiTextarea id="product-description" v-model="description" class="mt-2 min-h-36 resize-y" placeholder="商品の特徴や内容を入力" :aria-invalid="!!errors.description" />
                <UiFormMessage v-if="errors.description" class="mt-1" role="alert">{{ errors.description }}</UiFormMessage>
              </div>
            </div>
          </UiCard>

          <UiCard class="p-5 sm:p-6">
            <h2 class="text-lg sm:text-xl font-extrabold">商品画像 <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
              <label class="grid aspect-square cursor-pointer place-items-center rounded-xl border-2 border-dashed border-[#319663] bg-[#f7fbf8] text-center text-[#17834f] hover:bg-[#edf7f0]">
                <span><ImagePlus class="mx-auto size-7" aria-hidden="true" /><span class="mt-2 block text-sm font-bold">画像を追加</span><span class="mt-1 block text-xs text-[#718077]">複数選択可</span></span>
                <input class="sr-only" type="file" accept="image/jpeg,image/png,image/webp" multiple @change="addImages" />
              </label>
              <div v-for="(image, index) in images" :key="image.token" class="group relative aspect-square overflow-hidden rounded-xl border border-[#d4e0d7] bg-[#eef4ef]">
                <img :src="image.url" :alt="`商品画像 ${index + 1}`" class="size-full object-cover" />
                <span class="absolute left-2 top-2 rounded-full bg-white/95 px-2 py-0.5 text-xs font-bold text-[#17834f]">{{ index + 1 }}枚目</span>
                <button type="button" class="absolute right-2 top-2 grid size-7 place-items-center rounded-full bg-white text-[#c6453b] shadow" :aria-label="`商品画像 ${index + 1} を削除`" @click="removeImage(index)"><X class="size-4" aria-hidden="true" /></button>
                <div class="absolute inset-x-2 bottom-2 flex justify-between">
                  <button type="button" class="grid size-7 place-items-center rounded-full bg-white/95 text-[#176f45] shadow disabled:opacity-35" :disabled="index === 0" :aria-label="`商品画像 ${index + 1} を前へ移動`" @click="moveImage(index, -1)"><ChevronLeft class="size-4" /></button>
                  <button type="button" class="grid size-7 place-items-center rounded-full bg-white/95 text-[#176f45] shadow disabled:opacity-35" :disabled="index === images.length - 1" :aria-label="`商品画像 ${index + 1} を後ろへ移動`" @click="moveImage(index, 1)"><ChevronRight class="size-4" /></button>
                </div>
              </div>
            </div>
            <UiFormMessage v-if="errors.images" class="mt-2" role="alert">{{ errors.images }}</UiFormMessage>
          </UiCard>
        </div>

        <UiCard class="p-5 sm:p-6 lg:sticky lg:top-6">
          <h2 class="text-lg sm:text-xl font-extrabold">販売情報</h2>
          <div class="mt-5 grid gap-5">
            <div>
              <UiFormLabel for="price-yen" class="font-bold">商品価格（税込） <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></UiFormLabel>
              <div class="relative mt-2"><UiInput id="price-yen" v-model="priceYen" inputmode="numeric" class="h-12 pr-10" :aria-invalid="!!errors.priceYen" /><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#718077]">円</span></div>
              <p class="mt-1 text-xs text-[#718077]">送料を除いた商品の価格を入力してください。</p>
              <UiFormMessage v-if="errors.priceYen" class="mt-1" role="alert">{{ errors.priceYen }}</UiFormMessage>
            </div>

            <div class="border-t border-[#dce5de] pt-5">
              <h3 class="font-extrabold">送料設定 <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></h3>
              <p class="mt-1 text-xs text-[#718077]">配送先ごとの送料を入力してください。</p>
              <div class="mt-4 grid gap-4 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                <div>
                  <UiFormLabel for="fee-honshu" class="text-xs font-bold">本州・四国・九州</UiFormLabel>
                  <div class="relative mt-2"><UiInput id="fee-honshu" v-model="deliveryFeeHonshuYen" inputmode="numeric" class="h-11 pr-9" :aria-invalid="!!errors.deliveryFeeHonshuYen" /><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#718077]">円</span></div>
                  <UiFormMessage v-if="errors.deliveryFeeHonshuYen" class="mt-1" role="alert">{{ errors.deliveryFeeHonshuYen }}</UiFormMessage>
                </div>
                <div>
                  <UiFormLabel for="fee-hokkaido" class="text-xs font-bold">北海道</UiFormLabel>
                  <div class="relative mt-2"><UiInput id="fee-hokkaido" v-model="deliveryFeeHokkaidoYen" inputmode="numeric" class="h-11 pr-9" :aria-invalid="!!errors.deliveryFeeHokkaidoYen" /><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#718077]">円</span></div>
                  <UiFormMessage v-if="errors.deliveryFeeHokkaidoYen" class="mt-1" role="alert">{{ errors.deliveryFeeHokkaidoYen }}</UiFormMessage>
                </div>
                <div>
                  <UiFormLabel for="fee-okinawa" class="text-xs font-bold">沖縄</UiFormLabel>
                  <div class="relative mt-2"><UiInput id="fee-okinawa" v-model="deliveryFeeOkinawaYen" inputmode="numeric" class="h-11 pr-9" :aria-invalid="!!errors.deliveryFeeOkinawaYen" /><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#718077]">円</span></div>
                  <UiFormMessage v-if="errors.deliveryFeeOkinawaYen" class="mt-1" role="alert">{{ errors.deliveryFeeOkinawaYen }}</UiFormMessage>
                </div>
              </div>
            </div>

            <div class="rounded-xl border border-[#cfe0d4] bg-[#f1f8f3] p-4">
              <p class="text-sm font-extrabold">購入者表示価格（税込・送料込み）</p>
              <dl class="mt-3 grid gap-2 text-sm">
                <div class="flex justify-between"><dt>本州・四国・九州</dt><dd class="font-bold">¥{{ previewPrice(deliveryFeeHonshuYen) }}</dd></div>
                <div class="flex justify-between"><dt>北海道</dt><dd class="font-bold">¥{{ previewPrice(deliveryFeeHokkaidoYen) }}</dd></div>
                <div class="flex justify-between"><dt>沖縄</dt><dd class="font-bold">¥{{ previewPrice(deliveryFeeOkinawaYen) }}</dd></div>
              </dl>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
              <div>
                <UiFormLabel for="stock" class="font-bold">在庫数 <span class="rounded bg-[#d53b36] px-1.5 py-0.5 text-xs font-extrabold text-white">必須</span></UiFormLabel>
                <div class="relative mt-2"><UiInput id="stock" v-model="stockQuantity" inputmode="numeric" class="h-12 pr-10" :aria-invalid="!!errors.stockQuantity" /><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#718077]">個</span></div>
                <UiFormMessage v-if="errors.stockQuantity" class="mt-1" role="alert">{{ errors.stockQuantity }}</UiFormMessage>
              </div>
              <div>
                <UiFormLabel for="discount" class="font-bold">割引率（任意）</UiFormLabel>
                <div class="relative mt-2"><UiInput id="discount" v-model="discountPercent" inputmode="decimal" class="h-12 pr-10" placeholder="0" :aria-invalid="!!errors.discountPercent" /><span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[#718077]">%</span></div>
                <UiFormMessage v-if="errors.discountPercent" class="mt-1" role="alert">{{ errors.discountPercent }}</UiFormMessage>
              </div>
            </div>
          </div>
        </UiCard>
      </div>

      <UiFormMessage v-if="formError" class="mt-5 rounded-lg bg-[#fff1ef] p-3 text-center" role="alert">{{ formError }}</UiFormMessage>
      <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <UiButton type="submit" class="sm:order-3" :disabled="saveMutation.isPending.value">
          <LoaderCircle v-if="saveMutation.isPending.value" class="size-4 animate-spin" aria-hidden="true" />
          {{ isEdit && currentPublicationState === 'published' ? '更新して公開' : '公開する' }}
        </UiButton>
        <UiButton v-if="isEdit && currentPublicationState === 'published'" variant="outline" :disabled="saveMutation.isPending.value" @click="submit('unpublished')">非公開にする</UiButton>
        <UiButton v-else variant="outline" :disabled="saveMutation.isPending.value" @click="submit('draft')">下書き保存</UiButton>
        <UiButton as-child variant="outline" :disabled="saveMutation.isPending.value"><RouterLink to="/products">キャンセル</RouterLink></UiButton>
      </div>
    </form>
  </div>
</template>
