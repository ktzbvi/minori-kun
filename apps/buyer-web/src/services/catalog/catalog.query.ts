import { computed, shallowRef, watch, type Ref } from 'vue'
import { useInfiniteQuery, useQuery, useQueryClient, type InfiniteData } from '@tanstack/vue-query'
import type { components } from '@minorikun/api-contracts'
import api from '@/services/api'
import { buyerCatalogKeys } from './catalog.key'

export type BuyerCatalogueProduct = components['schemas']['BuyerCatalogueProductResource']
export type BuyerCatalogueVariant = BuyerCatalogueProduct['variants'][number]
type BuyerProductFeed = components['schemas']['BuyerProductFeedResource']

export async function fetchBuyerCatalogueProducts() {
  return (await api.get<{ data: BuyerCatalogueProduct[] }>('/api/v1/buyer/products')).data.data
}

export async function fetchBuyerCatalogueProductsByVariants(
  variantIds: string[],
  signal?: AbortSignal,
) {
  if (!variantIds.length) return []

  return (
    await api.get<{ data: BuyerCatalogueProduct[] }>('/api/v1/buyer/products', {
      params: { variant_ids: [...new Set(variantIds)] },
      signal,
    })
  ).data.data
}

export function useBuyerCatalogueQuery() {
  return useQuery({
    queryKey: buyerCatalogKeys.products(),
    queryFn: fetchBuyerCatalogueProducts,
    staleTime: 30_000,
  })
}

export function useBuyerProductFeedQuery(category: Ref<string>) {
  const queryClient = useQueryClient()
  const feedCategory = shallowRef(category.value)
  watch(
    category,
    (nextCategory) => {
      const queryKey = buyerCatalogKeys.feed(nextCategory)
      void queryClient.cancelQueries({ queryKey, exact: true })
      const updatedAt = queryClient.getQueryState(queryKey)?.dataUpdatedAt
      // A filter change resets pagination; leaving Home keeps the full cached feed.
      queryClient.setQueryData<InfiniteData<BuyerProductFeed, string | null>>(
        queryKey,
        (data) =>
          data && {
            pages: data.pages.slice(0, 1),
            pageParams: data.pageParams.slice(0, 1),
          },
        { updatedAt },
      )
      // Switch the observed query only after its cached pages have been reset.
      feedCategory.value = nextCategory
    },
    { flush: 'sync' },
  )
  return useInfiniteQuery({
    queryKey: computed(() => buyerCatalogKeys.feed(feedCategory.value)),
    initialPageParam: null as string | null,
    queryFn: async ({ pageParam, signal, queryKey }) =>
      (
        await api.get<{ data: BuyerProductFeed }>('/api/v1/buyer/products/feed', {
          params: {
            per_page: 4,
            category: queryKey[3] === 'all' ? undefined : queryKey[3],
            cursor: pageParam ?? undefined,
          },
          signal,
        })
      ).data.data,
    getNextPageParam: (lastPage) => lastPage.next_cursor ?? undefined,
    gcTime: 5 * 60 * 1000,
    staleTime: 30_000,
    retry: false,
    refetchOnWindowFocus: false,
  })
}
