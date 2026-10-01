export type BuyerAccountProfile = {
  member_id: string
  name: string
  name_phonetic: string
  email: string
  pending_email: string | null
  phone: string
  postal_code: string
  prefecture: string
  city: string
  address_line1: string
  address_line2: string
}

export type BuyerAccountProfileUpdate = Omit<BuyerAccountProfile, 'member_id' | 'pending_email'>
