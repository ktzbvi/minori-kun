export const buyerCatalogKeys = {
  all: () => ['buyer', 'catalog'] as const,
  feed: (category: string) => [...buyerCatalogKeys.all(), 'feed', category] as const,
  products: () => [...buyerCatalogKeys.all(), 'products'] as const,
}
