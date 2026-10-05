export const buyerOrderKeys = {
  all: () => ['buyer', 'orders'] as const,
  list: (filters: Record<string, string>) => [...buyerOrderKeys.all(), 'list', filters] as const,
  detail: (id: string) => [...buyerOrderKeys.all(), 'detail', id] as const,
  paymentMode: () => [...buyerOrderKeys.all(), 'payment-mode'] as const,
}
