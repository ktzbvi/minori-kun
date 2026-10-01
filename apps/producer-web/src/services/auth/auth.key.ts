import { producerKeys } from '@/services/producer.key'

export const producerAuthKeys = {
  currentSession: () => [...producerKeys.all(), 'session'] as const,
}
