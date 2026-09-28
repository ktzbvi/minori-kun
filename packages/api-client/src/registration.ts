import type { AxiosInstance } from 'axios'

export type BuyerRegistrationStatus = {
  email: string
  verified: boolean
  otp_expires_at: string
  resend_available_at: string
}

export function createBuyerRegistrationApi(client: AxiosInstance) {
  const prefix = '/api/v1/buyer/registration'

  return {
    start: (email: string) =>
      client.post<{
        data: Omit<BuyerRegistrationStatus, 'verified'> & { next: 'verify-otp' }
      }>(`${prefix}/start`, { email }),
    status: () => client.get<{ data: BuyerRegistrationStatus }>(`${prefix}/status`),
    resendOtp: () =>
      client.post<{
        data: Omit<BuyerRegistrationStatus, 'verified'>
      }>(`${prefix}/resend-otp`),
    verifyOtp: (code: string) =>
      client.post<{ data: { next: 'registration-details' } }>(`${prefix}/verify-otp`, { code }),
    complete: (details: Record<string, string | boolean>) =>
      client.post<{ data: { redirect: string } }>(`${prefix}/complete`, details),
  }
}
