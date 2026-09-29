export type ApiErrorPayload = {
  message?: string
  code?: string
  errors?: Record<string, string[]>
  retry_after?: number
  resend_available_at?: string | null
  server_time?: string
}

export function getApiErrorPayload(error: unknown): ApiErrorPayload | undefined {
  if (typeof error !== 'object' || error === null || !('response' in error)) return undefined

  const response = error.response
  if (typeof response !== 'object' || response === null || !('data' in response)) return undefined

  const body = response.data
  if (typeof body !== 'object' || body === null) return undefined
  const payload = 'error' in body && typeof body.error === 'object' && body.error !== null ? body.error : body

  return {
    message: 'message' in payload && typeof payload.message === 'string' ? payload.message : undefined,
    code: 'code' in payload && typeof payload.code === 'string' ? payload.code : undefined,
    errors: 'errors' in payload && typeof payload.errors === 'object' && payload.errors !== null
      ? payload.errors as Record<string, string[]>
      : undefined,
    retry_after: 'retry_after' in payload && typeof payload.retry_after === 'number' ? payload.retry_after : undefined,
    resend_available_at: 'resend_available_at' in payload && typeof payload.resend_available_at === 'string'
      ? payload.resend_available_at
      : undefined,
    server_time: 'server_time' in payload && typeof payload.server_time === 'string' ? payload.server_time : undefined,
  }
}

export function getApiErrorMessage(error: unknown): string | undefined {
  const payload = getApiErrorPayload(error)
  if (payload?.message) return payload.message
  const fieldMessage = getFirstApiFieldError(error)
  if (fieldMessage) return fieldMessage
  if (typeof error !== 'object' || error === null || !('response' in error)) {
    return error instanceof Error ? error.message : undefined
  }

  const response = error.response
  if (typeof response !== 'object' || response === null || !('data' in response)) return undefined

  const data = response.data
  if (typeof data !== 'object' || data === null || !('message' in data)) return undefined

  return typeof data.message === 'string' ? data.message : undefined
}

export function getFirstApiFieldError(error: unknown): string | undefined {
  const errors = getApiErrorPayload(error)?.errors
  if (!errors) return undefined

  for (const messages of Object.values(errors)) {
    const message = messages[0]
    if (message) return message
  }

  return undefined
}
