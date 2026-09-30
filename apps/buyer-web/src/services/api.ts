import axios, { type InternalAxiosRequestConfig } from 'axios'

const apiBaseUrl =
  window.__MINORI_CONFIG__?.apiOrigin ?? import.meta.env.VITE_API_ORIGIN ?? 'http://localhost:8000'
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

export async function ensureCsrfCookie(force = false) {
  if (!force && hasXsrfCookie()) return

  csrfCookieRequest ??= csrfApi
    .get('/sanctum/csrf-cookie')
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

type RetryableLoginConfig = InternalAxiosRequestConfig & {
  csrfRecoveryAttempted?: boolean
}

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const config = error.config as RetryableLoginConfig | undefined
    const status = error.response?.status
    const isLoginRequest = config?.url?.includes('/auth/login')

    if (
      !config ||
      config.csrfRecoveryAttempted ||
      !isLoginRequest ||
      (status !== 401 && status !== 419)
    ) {
      return Promise.reject(error)
    }

    config.csrfRecoveryAttempted = true

    try {
      await ensureCsrfCookie(true)

      return api.request(config)
    } catch {
      return Promise.reject(error)
    }
  },
)

export default api
