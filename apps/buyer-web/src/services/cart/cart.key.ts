import type { GuestCartLine } from '@/lib/guest-cart'

export const buyerCartKeys = {
  all: () => ['buyer', 'cart'] as const,
  current: () => [...buyerCartKeys.all(), 'current'] as const,
  counts: () => [...buyerCartKeys.all(), 'count'] as const,
  count: (buyerId: string, guestItems: GuestCartLine[]) =>
    [...buyerCartKeys.counts(), buyerId, guestItems] as const,
}
