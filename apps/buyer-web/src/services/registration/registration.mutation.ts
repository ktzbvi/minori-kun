import type { BuyerRegistrationCompletion, BuyerRegistrationStatus } from '@/types/registration'
import api from '../api'

export async function startBuyerRegistration(email: string) {
  return (await api.post<{ data: Omit<BuyerRegistrationStatus, 'verified'> & { next: 'verify-otp' } }>(
    '/api/v1/buyer/registration/start',
    { email },
  )).data.data
}

export async function resendBuyerRegistrationOtp() {
  return (await api.post<{ data: Omit<BuyerRegistrationStatus, 'verified'> }>(
    '/api/v1/buyer/registration/resend-otp',
  )).data.data
}

export async function verifyBuyerRegistrationOtp(code: string) {
  return (await api.post<{ data: { next: 'registration-details' } }>('/api/v1/buyer/registration/verify-otp', {
    code,
  })).data.data
}

export async function completeBuyerRegistration(details: BuyerRegistrationCompletion) {
  return (await api.post<{ data: { redirect: string } }>('/api/v1/buyer/registration/complete', details)).data.data
}
