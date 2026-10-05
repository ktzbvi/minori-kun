import type { operations } from '@minorikun/api-contracts'

export type ProducerPasswordResetStart =
  operations['producerPasswordReset.start']['requestBody']['content']['application/json']
export type ProducerPasswordResetValidation =
  operations['producerPasswordReset.validateToken']['requestBody']['content']['application/json']
export type ProducerPasswordResetCompletion =
  operations['producerPasswordReset.complete']['requestBody']['content']['application/json']
