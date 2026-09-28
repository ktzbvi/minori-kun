import axios from 'axios'
import { createApiClient, createAuthApi } from '@minorikun/api-client'

export const apiClient = createApiClient({
  baseURL: window.__MINORI_CONFIG__?.apiOrigin ?? import.meta.env.VITE_API_ORIGIN ?? 'http://localhost:8000',
})

export const adminAuthApi = createAuthApi(apiClient, 'admin')

interface ApiErrorBody {
  message?: unknown
}

export function getApiErrorMessage(error: unknown): string | undefined {
  if (axios.isAxiosError<ApiErrorBody>(error)) {
    const message = error.response?.data?.message
    if (typeof message === 'string' && message.trim()) return message
  }

  return error instanceof Error && error.message ? error.message : undefined
}
