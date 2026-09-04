import axios, { type AxiosInstance } from 'axios'

export type ApiClientOptions = {
  baseURL: string
  onUnauthorized?: () => void
}

export function createApiClient(options: ApiClientOptions): AxiosInstance {
  const client = axios.create({
    baseURL: options.baseURL,
    headers: { Accept: 'application/json' },
    withCredentials: true,
    withXSRFToken: true,
  })

  client.interceptors.response.use(
    (response) => response,
    (error: unknown) => {
      if (axios.isAxiosError(error) && error.response?.status === 401) {
        options.onUnauthorized?.()
      }
      return Promise.reject(error)
    },
  )

  return client
}
