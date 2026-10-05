export const buyerCatalogKeys = {
  all: () => ['buyer', 'catalog'] as const,
  products: () => [...buyerCatalogKeys.all(), 'products'] as const,
}
