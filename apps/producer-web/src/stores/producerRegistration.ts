import { ref } from 'vue'
import { defineStore } from 'pinia'

export type ProducerRegistrationDetails = {
  shopName: string
  contactName: string
  phone: string
  password: string
  photo: File | null
  acceptedTerms: boolean
}

export const useProducerRegistrationStore = defineStore('producer-registration', () => {
  const email = ref('')
  const verificationCode = ref('')
  const emailVerified = ref(false)
  const submitted = ref(false)

  // UI boundary for future API mutations. The pages do not need to change when
  // these action bodies are replaced with calls through packages/api-client.
  async function requestVerificationCode(nextEmail: string) {
    email.value = nextEmail
    verificationCode.value = ''
    emailVerified.value = false
  }

  async function verifyEmail(code: string) {
    verificationCode.value = code
    emailVerified.value = true
  }

  async function resendVerificationCode() {
    verificationCode.value = ''
  }

  function changeEmail() {
    verificationCode.value = ''
    emailVerified.value = false
  }

  async function createAccount(_details: ProducerRegistrationDetails) {
    submitted.value = true
  }

  return { email, verificationCode, emailVerified, submitted, requestVerificationCode, verifyEmail, resendVerificationCode, changeEmail, createAccount }
})
