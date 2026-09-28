import { createApiClient, createAuthApi, createBuyerRegistrationApi } from '@minorikun/api-client'

export const apiClient = createApiClient({
  baseURL:
    window.__MINORI_CONFIG__?.apiOrigin ??
    import.meta.env.VITE_API_ORIGIN ??
    'http://localhost:8000',
})

export const authApi = createAuthApi(apiClient, 'buyer')
export const buyerRegistrationApi = createBuyerRegistrationApi(apiClient)
