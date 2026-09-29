import axios from 'axios'

const api = axios.create({
  baseURL: window.__MINORI_CONFIG__?.apiOrigin ?? import.meta.env.VITE_API_ORIGIN ?? 'http://localhost:8000',
  headers: { Accept: 'application/json' },
  withCredentials: true,
  withXSRFToken: true,
})

export default api
