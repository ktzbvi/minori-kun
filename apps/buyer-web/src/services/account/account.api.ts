import type { BuyerAccountProfile, BuyerAccountProfileUpdate } from '@/types/account'
import api from '../api'

export async function getBuyerAccountProfile(): Promise<BuyerAccountProfile> {
  return (await api.get<{ data: BuyerAccountProfile }>('/api/v1/buyer/account/profile')).data.data
}

export async function updateBuyerAccountProfile(values: BuyerAccountProfileUpdate) {
  return (
    await api.patch<{ data: { profile: BuyerAccountProfile; email_change_pending: boolean } }>(
      '/api/v1/buyer/account/profile',
      values,
    )
  ).data.data
}

export async function changeBuyerPassword(values: {
  current_password: string
  password: string
  password_confirmation: string
}) {
  return (await api.put<{ data: { changed: boolean } }>('/api/v1/buyer/account/password', values))
    .data.data
}
