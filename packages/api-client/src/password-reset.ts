import type { AxiosInstance } from 'axios'

export function createBuyerPasswordResetApi(client: AxiosInstance) {
  const prefix = '/api/v1/buyer/password-reset'

  return {
    start: (email: string) =>
      client.post<{ data: { message: string } }>(`${prefix}/start`, { email }),
    complete: (details: {
      email: string
      token: string
      password: string
      password_confirmation: string
    }) => client.post<{ data: { redirect: string } }>(`${prefix}/complete`, details),
  }
}
