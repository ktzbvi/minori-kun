import api from '../api'

export type BuyerPasswordResetCompletion = {
  email: string
  token: string
  password: string
  password_confirmation: string
}

export async function startBuyerPasswordReset(email: string) {
  return (await api.post<{ data: { message: string } }>('/api/v1/buyer/password-reset/start', { email })).data.data
}

export async function completeBuyerPasswordReset(details: BuyerPasswordResetCompletion) {
  return (await api.post<{ data: { redirect: string } }>('/api/v1/buyer/password-reset/complete', details)).data.data
}
