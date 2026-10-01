import axios from 'axios'
import { queryClient } from '@/lib/query'
import { producerKeys } from '@/services/producer.key'

const apiBaseUrl = window.__MINORI_CONFIG__?.apiOrigin ?? import.meta.env.VITE_API_ORIGIN ?? 'http://localhost:8000'
const unsafeMethods = new Set(['post', 'put', 'patch', 'delete'])

const api = axios.create({
  baseURL: apiBaseUrl,
  headers: { Accept: 'application/json' },
  withCredentials: true,
  withXSRFToken: true,
})

const csrfApi = axios.create({
  baseURL: apiBaseUrl,
  headers: { Accept: 'application/json' },
  withCredentials: true,
})

let csrfCookieRequest: Promise<void> | undefined

function hasXsrfCookie() {
  return document.cookie.split('; ').some((cookie) => cookie.startsWith('XSRF-TOKEN='))
}

export async function ensureCsrfCookie() {
  if (hasXsrfCookie()) return

  csrfCookieRequest ??= csrfApi.get('/sanctum/csrf-cookie')
    .then(() => undefined)
    .finally(() => {
      csrfCookieRequest = undefined
    })

  await csrfCookieRequest
}

api.interceptors.request.use(async (config) => {
  const method = config.method?.toLowerCase() ?? 'get'

  if (unsafeMethods.has(method)) {
    await ensureCsrfCookie()
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  async (error: unknown) => {
    const protectedPortalPath = ['/dashboard', '/products', '/orders', '/sales', '/onboarding']
      .some((path) => window.location.pathname === path || window.location.pathname.startsWith(`${path}/`))

    if (axios.isAxiosError(error) && error.response?.status === 401 && protectedPortalPath) {
      queryClient.removeQueries({ queryKey: producerKeys.all() })
      const redirect = `${window.location.pathname}${window.location.search}${window.location.hash}`
      window.location.replace(`/login?redirect=${encodeURIComponent(redirect)}`)
    }

    return Promise.reject(error)
  },
)

export default api
