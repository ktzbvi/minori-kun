import { useQuery } from '@tanstack/vue-query'
import api from '@/services/api'
import type {
  ProducerOnboardingStatus,
  ProducerRegistrationDetails,
  ProducerRegistrationState,
  ProducerTerms,
} from '@/types/registration'
import { producerRegistrationKeys } from './registration.key'

export function producerRegistrationStatusQueryOptions() {
  return {
    queryKey: producerRegistrationKeys.status(),
    queryFn: async () => {
      return (await api.get<{ data: ProducerRegistrationState }>('/api/v1/producer/registration')).data.data
    },
    staleTime: 0,
  }
}

export function producerTermsQueryOptions() {
  return {
    queryKey: producerRegistrationKeys.terms(),
    queryFn: async () => {
      return (await api.get<{ data: ProducerTerms }>('/api/v1/producer/terms')).data.data
    },
    staleTime: 0,
  }
}

export function useProducerRegistrationStatusQuery() {
  return useQuery<ProducerRegistrationState>({
    queryKey: producerRegistrationKeys.status(),
    queryFn: async () => {
      return (await api.get<{ data: ProducerRegistrationState }>('/api/v1/producer/registration')).data.data
    },
    staleTime: 0,
  })
}

export function useProducerRegistrationDetailsQuery() {
  return useQuery<ProducerRegistrationDetails>({
    queryKey: producerRegistrationKeys.details(),
    queryFn: async () => {
      return (await api.get<{ data: ProducerRegistrationDetails }>('/api/v1/producer/registration/details')).data.data
    },
    staleTime: 0,
  })
}

export function useProducerTermsQuery(enabled = true) {
  return useQuery<ProducerTerms>({
    queryKey: producerRegistrationKeys.terms(),
    queryFn: async () => {
      return (await api.get<{ data: ProducerTerms }>('/api/v1/producer/terms')).data.data
    },
    staleTime: 0,
    enabled,
  })
}

export function useProducerOnboardingQuery() {
  return useQuery<ProducerOnboardingStatus>({
    queryKey: producerRegistrationKeys.onboarding(),
    queryFn: async () => {
      return (await api.get<{ data: ProducerOnboardingStatus }>('/api/v1/producer/onboarding')).data.data
    },
    staleTime: 0,
  })
}
