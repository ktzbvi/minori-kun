export const producerRegistrationKeys = {
  status: () => ['producer-registration', 'status'] as const,
  details: () => ['producer-registration', 'details'] as const,
  terms: () => ['producer-registration', 'terms'] as const,
  onboarding: () => ['producer-onboarding'] as const,
}
