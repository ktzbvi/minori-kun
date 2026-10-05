import type { components } from '@minorikun/api-contracts'

export type CurrentSession = components['schemas']['CurrentSessionResource']

export type ProducerLoginCredentials = { email: string; password: string }
