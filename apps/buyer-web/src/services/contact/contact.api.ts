import api from '@/services/api'

type CreateBuyerInquiryValues = {
  subject: string
  message: string
  idempotency_key: string
}

type CreateBuyerInquiryResponse = {
  data: {
    reference_number: string
  }
}

export async function createBuyerInquiry(values: CreateBuyerInquiryValues) {
  const response = await api.post<CreateBuyerInquiryResponse>('/api/v1/buyer/inquiries', values)

  return response.data.data
}
