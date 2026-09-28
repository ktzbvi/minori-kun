import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  back: vi.fn(),
  csrf: vi.fn(),
  start: vi.fn(),
  push: vi.fn(),
  error: vi.fn(),
}))

vi.mock('vue-router', () => ({
  RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
  useRoute: () => ({ query: {} }),
  useRouter: () => ({ back: mocks.back, push: mocks.push }),
}))

vi.mock('@minorikun/ui', () => ({
  UiButton: { template: '<button><slot /></button>' },
  UiCard: { template: '<section><slot /></section>' },
  toast: { error: mocks.error },
}))

vi.mock('@/lib/api', () => ({
  authApi: { csrf: mocks.csrf },
  buyerRegistrationApi: { start: mocks.start },
}))

import RegisterEmailPage from './RegisterEmailPage.vue'

describe('Buyer registration email entry', () => {
  beforeEach(() => vi.clearAllMocks())

  it('shows the first registration step and a Login link', () => {
    const wrapper = mount(RegisterEmailPage)

    expect(wrapper.text()).toContain('\u65b0\u898f\u4f1a\u54e1\u767b\u9332')
    expect(wrapper.text()).toContain('\u2460 \u30e1\u30fc\u30eb\u5165\u529b')
    expect(wrapper.find('a[href="/login"]').exists()).toBe(true)
  })

  it('requires a valid email before requesting a code', async () => {
    const wrapper = mount(RegisterEmailPage)

    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('#registration-email-error').exists()).toBe(true))

    expect(mocks.start).not.toHaveBeenCalled()

    await wrapper.get('#registration-email').setValue('buyer@example.test')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(mocks.csrf).toHaveBeenCalledOnce())

    expect(mocks.start).toHaveBeenCalledWith('buyer@example.test')
    expect(mocks.push).toHaveBeenCalledWith({ name: 'register-verify', query: {} })
  })
})
