import axios from 'axios'

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

export default api
