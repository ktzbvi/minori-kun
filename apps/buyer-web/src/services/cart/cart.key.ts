export const buyerCartKeys = {
  all: () => ['buyer', 'cart'] as const,
  current: () => [...buyerCartKeys.all(), 'current'] as const,
}
