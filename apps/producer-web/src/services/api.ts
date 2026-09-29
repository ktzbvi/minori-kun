import axios from 'axios'

const api = axios.create({
  baseURL: window.__MINORI_CONFIG__?.apiOrigin ?? import.meta.env.VITE_API_ORIGIN ?? 'http://localhost:8000',
  headers: { Accept: 'application/json' },
  withCredentials: true,
  withXSRFToken: true,
})

let csrfCookieRequest: Promise<void> | undefined

function hasXsrfCookie() {
  return document.cookie.split('; ').some((cookie) => cookie.startsWith('XSRF-TOKEN='))
}

export async function ensureCsrfCookie() {
  if (hasXsrfCookie()) return

  csrfCookieRequest ??= api.get('/sanctum/csrf-cookie')
    .then(() => undefined)
    .finally(() => {
      csrfCookieRequest = undefined
    })

  await csrfCookieRequest
}

export default api
