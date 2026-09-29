import type { components } from '@minorikun/api-contracts'
import type { CurrentSession } from '@/types/auth'

export type ProducerRegistrationState = components['schemas']['ProducerRegistrationStateResource']
export type ProducerRegistrationPhoto = components['schemas']['ProducerRegistrationPhotoResource']
export type ProducerTerms = components['schemas']['ProducerTermsResource']
export type ProducerRegistrationDetails = components['schemas']['ProducerRegistrationDetailsResource']
export type ProducerRegistrationCompletion = components['schemas']['CompleteProducerRegistrationRequest']
export type ProducerOnboardingStatus = components['schemas']['ProducerOnboardingStatusResource']
export type ProducerRegistrationCompleteResponse = CurrentSession
