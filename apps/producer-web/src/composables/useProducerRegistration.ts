import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { getApiErrorMessage, getApiErrorPayload } from '@/lib/api-error'
import type { ProducerRegistrationCompletion } from '@/types/registration'
import {
  useCompleteRegistrationMutation,
  useDeleteRegistrationPhotoMutation,
  useRequestRegistrationCodeMutation,
  useResetRegistrationMutation,
  useResendRegistrationCodeMutation,
  useUploadRegistrationPhotoMutation,
  useVerifyRegistrationCodeMutation,
} from '@/services/registration/registration.mutation'

export function useProducerRegistration() {
  const router = useRouter()
  const requestCodeMutation = useRequestRegistrationCodeMutation()
  const resendMutation = useResendRegistrationCodeMutation()
  const verifyMutation = useVerifyRegistrationCodeMutation()
  const resetMutation = useResetRegistrationMutation()
  const uploadPhotoMutation = useUploadRegistrationPhotoMutation()
  const deletePhotoMutation = useDeleteRegistrationPhotoMutation()
  const completeMutation = useCompleteRegistrationMutation()

  const isRequestingCode = computed(() => requestCodeMutation.isPending.value || resetMutation.isPending.value)
  const isVerifyingCode = computed(() => verifyMutation.isPending.value)
  const isResendingCode = computed(() => resendMutation.isPending.value)
  const isResettingRegistration = computed(() => resetMutation.isPending.value)
  const isUploadingPhoto = computed(() => uploadPhotoMutation.isPending.value)
  const isDeletingPhoto = computed(() => deletePhotoMutation.isPending.value)
  const isCompletingRegistration = computed(() => completeMutation.isPending.value)

  function errorMessage(error: unknown) {
    return getApiErrorMessage(error) ?? ''
  }

  async function requestCode(email: string, currentState?: string) {
    if (currentState === 'expired' || currentState === 'consumed') await resetMutation.mutateAsync()
    const registration = await requestCodeMutation.mutateAsync(email)
    if (registration.state === 'pending') await router.push({ name: 'register-verify' })

    return registration
  }

  async function resendCode() {
    return await resendMutation.mutateAsync()
  }

  async function verifyCode(code: string) {
    const registration = await verifyMutation.mutateAsync(code)
    if (registration.state === 'verified') await router.replace({ name: 'register-details' })

    return registration
  }

  async function changeEmail() {
    await resetMutation.mutateAsync()
    await router.replace({ name: 'register' })
  }

  async function uploadPhoto(file: File) {
    return await uploadPhotoMutation.mutateAsync(file)
  }

  async function deletePhoto(id: string) {
    return await deletePhotoMutation.mutateAsync(id)
  }

  async function completeRegistration(payload: ProducerRegistrationCompletion) {
    const session = await completeMutation.mutateAsync(payload)
    await router.replace({ name: 'onboarding' })

    return session
  }

  function resetVerificationState() {
    verifyMutation.reset()
    resendMutation.reset()
  }

  function resetPhotoState() {
    uploadPhotoMutation.reset()
    deletePhotoMutation.reset()
  }

  function resetCompletionState() {
    completeMutation.reset()
  }

  function serverErrors(error: unknown) {
    return getApiErrorPayload(error)?.errors
  }

  return {
    isRequestingCode,
    isVerifyingCode,
    isResendingCode,
    isResettingRegistration,
    isUploadingPhoto,
    isDeletingPhoto,
    isCompletingRegistration,
    errorMessage,
    serverErrors,
    requestCode,
    resendCode,
    verifyCode,
    changeEmail,
    uploadPhoto,
    deletePhoto,
    completeRegistration,
    resetVerificationState,
    resetPhotoState,
    resetCompletionState,
  }
}
