export type BuyerRegistrationStatus = {
  email: string
  verified: boolean
  otp_expires_at: string
  resend_available_at: string
}

export type BuyerRegistrationCompletion = {
  name: string
  name_phonetic: string
  phone: string
  postal_code: string
  prefecture: string
  city: string
  address_line1: string
  address_line2: string
  password: string
  password_confirmation: string
  terms_accepted: boolean
}
