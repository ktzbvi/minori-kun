import type { CurrentSession } from '@/types/auth'
import api from '../api'

export type BuyerLoginCredentials = { email: string; password: string }

export async function loginBuyer({ email, password }: BuyerLoginCredentials): Promise<CurrentSession> {
  return (await api.post<{ data: CurrentSession }>('/api/v1/buyer/auth/login', { email, password })).data.data
}
