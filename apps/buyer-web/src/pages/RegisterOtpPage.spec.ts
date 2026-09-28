import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  csrf: vi.fn(),
  status: vi.fn(),
  verifyOtp: vi.fn(),
  resendOtp: vi.fn(),
  push: vi.fn(),
  replace: vi.fn(),
  back: vi.fn(),
  success: vi.fn(),
  error: vi.fn(),
}))

vi.mock('vue-router', () => ({
  useRoute: () => ({ query: {} }),
  useRouter: () => ({
    push: mocks.push,
    replace: mocks.replace,
    back: mocks.back,
    hasRoute: () => true,
  }),
}))
vi.mock('@/lib/api', () => ({
  authApi: { csrf: mocks.csrf },
  buyerRegistrationApi: {
    status: mocks.status,
    verifyOtp: mocks.verifyOtp,
    resendOtp: mocks.resendOtp,
  },
}))
vi.mock('@minorikun/ui', () => ({
  UiButton: { template: '<button><slot /></button>' },
  UiCard: { template: '<section><slot /></section>' },
  toast: { success: mocks.success, error: mocks.error },
}))

import RegisterOtpPage from './RegisterOtpPage.vue'

function statusResponse() {
  return {
    data: {
      data: {
        email: 'buyer@example.test',
        verified: false,
        otp_expires_at: '2026-09-28T10:03:00+00:00',
        resend_available_at: '2026-09-28T10:03:00+00:00',
      },
    },
  }
}

describe('Buyer registration OTP', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mocks.status.mockResolvedValue(statusResponse())
  })

  it('loads the server-side registration status', async () => {
    const wrapper = mount(RegisterOtpPage)
    await flushPromises()

    expect(mocks.status).toHaveBeenCalledOnce()
    expect(wrapper.text()).toContain('buyer@example.test')
  })

  it('verifies a six-digit code and advances to the details step', async () => {
    const wrapper = mount(RegisterOtpPage)
    await flushPromises()
    await wrapper.get('#registration-otp').setValue('123456')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(mocks.csrf).toHaveBeenCalledOnce())

    expect(mocks.verifyOtp).toHaveBeenCalledWith('123456')
    expect(mocks.push).toHaveBeenCalledWith({ name: 'register-details', query: {} })
  })
})
