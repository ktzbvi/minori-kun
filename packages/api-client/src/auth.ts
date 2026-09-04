import type { AxiosInstance } from 'axios'

export type Portal = 'buyer' | 'producer' | 'admin'
export type UserRole = Portal

export type CurrentSession = {
  id: string
  role: UserRole
  email: string
  email_verified: boolean
  display_name: string | null
  producer?: {
    eligible_to_sell: boolean
    visa_status: string | null
    mastercard_status: string | null
  }
}

export function createAuthApi(client: AxiosInstance, portal: Portal) {
  const prefix = `/api/v1/${portal}`

  return {
    csrf: () => client.get('/sanctum/csrf-cookie'),
    login: (email: string, password: string) =>
      client.post<{ data: CurrentSession }>(`${prefix}/auth/login`, { email, password }),
    me: () => client.get<{ data: CurrentSession }>(`${prefix}/me`),
    logout: () => client.post(`${prefix}/auth/logout`),
  }
}
