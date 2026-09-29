import type { BuyerRegistrationStatus } from '@/types/registration'
import api from '../api'

export async function getBuyerRegistrationStatus(): Promise<BuyerRegistrationStatus> {
  return (await api.get<{ data: BuyerRegistrationStatus }>('/api/v1/buyer/registration/status')).data.data
}
