import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useBuyerCartQuery } from '@/services/cart/cart.query'
import { useBuyerCatalogueQuery } from '@/services/catalog/catalog.query'

export function useBuyerCategory() {
  const router = useRouter()
  const catalogueQuery = useBuyerCatalogueQuery()
  const cartQuery = useBuyerCartQuery()
  const cartItemCount = computed(
    () => cartQuery.data.value?.items.reduce((total, item) => total + item.quantity, 0) ?? 0,
  )
  const categoryCards = computed(() => [
    { id: 'all', label: 'すべて', count: catalogueQuery.data.value?.length ?? 0 },
    ...Array.from(
      new Set((catalogueQuery.data.value ?? []).map((product) => product.category).filter(Boolean)),
    ).map((category) => ({
      id: category!,
      label: category!,
      count:
        catalogueQuery.data.value?.filter((product) => product.category === category).length ?? 0,
    })),
  ])
  function openCategory(categoryId: string) {
    void router.push({ name: 'category-products', params: { categoryId } })
  }
  function openSearch() {
    void router.push({ name: 'search' })
  }
  function openCart() {
    void router.push({ name: 'cart' })
  }
  return {
    router,
    catalogueQuery,
    cartQuery,
    cartItemCount,
    categoryCards,
    openCategory,
    openSearch,
    openCart,
  }
}
