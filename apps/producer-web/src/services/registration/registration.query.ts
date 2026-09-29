import api from '@/services/api'
import type {
  ProducerOnboardingStatus,
  ProducerRegistrationDetails,
  ProducerRegistrationState,
  ProducerTerms,
} from '@/types/registration'
import { producerRegistrationKeys } from './registration.key'

export const producerRegistrationStatusQuery = {
  queryKey: producerRegistrationKeys.status(),
  queryFn: async () => (await api.get<{ data: ProducerRegistrationState }>('/api/v1/producer/registration')).data.data,
  staleTime: 0,
}

export const producerRegistrationDetailsQuery = {
  queryKey: producerRegistrationKeys.details(),
  queryFn: async () =>
    (await api.get<{ data: ProducerRegistrationDetails }>('/api/v1/producer/registration/details')).data.data,
  staleTime: 0,
}

export const producerTermsQuery = {
  queryKey: producerRegistrationKeys.terms(),
  queryFn: async () => (await api.get<{ data: ProducerTerms }>('/api/v1/producer/terms')).data.data,
  staleTime: 0,
}

export const producerOnboardingQuery = {
  queryKey: producerRegistrationKeys.onboarding(),
  queryFn: async () => (await api.get<{ data: ProducerOnboardingStatus }>('/api/v1/producer/onboarding')).data.data,
  staleTime: 0,
}
