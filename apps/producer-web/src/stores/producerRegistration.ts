import { ref } from 'vue'
import { defineStore } from 'pinia'

// Only reversible profile fields live here. OTPs, passwords, consent tokens,
// and temporary photo credentials remain in their owning page/mutation.
export const useProducerRegistrationStore = defineStore('producer-registration', () => {
  const shopName = ref('')
  const contactName = ref('')
  const phone = ref('')

  return { shopName, contactName, phone }
})
