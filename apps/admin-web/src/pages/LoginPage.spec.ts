import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  csrf: vi.fn(),
  login: vi.fn(),
  invalidateQueries: vi.fn(),
  replace: vi.fn(),
}))

vi.mock('@/lib/api', () => ({ authApi: { csrf: mocks.csrf, login: mocks.login } }))
vi.mock('@/lib/query', () => ({ queryClient: { invalidateQueries: mocks.invalidateQueries } }))
vi.mock('vue-router', () => ({
  useRoute: () => ({ query: {} }),
  useRouter: () => ({ replace: mocks.replace }),
}))

import LoginPage from './LoginPage.vue'

describe('Admin Login', () => {
  beforeEach(() => vi.clearAllMocks())

  it('does not prefill administrator credentials', () => {
    const wrapper = mount(LoginPage)
    expect((wrapper.get('#admin-email').element as HTMLInputElement).value).toBe('')
    expect((wrapper.get('#admin-password').element as HTMLInputElement).value).toBe('')
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeDefined()
  })

  it('uses the existing Sanctum authentication flow and opens the dashboard', async () => {
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(mocks.csrf).toHaveBeenCalledOnce()
    expect(mocks.login).toHaveBeenCalledWith('admin@example.test', 'secret-password')
    expect(mocks.invalidateQueries).toHaveBeenCalledWith({ queryKey: ['current-session'] })
    expect(mocks.replace).toHaveBeenCalledWith('/')
  })

  it('shows a neutral error and clears the password after authentication fails', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 422 } })
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('wrong-password')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(wrapper.get('[role="alert"]').text()).toBe(
      'メールアドレスまたはパスワードを確認してください。',
    )
    expect((wrapper.get('#admin-password').element as HTMLInputElement).value).toBe('')
  })

  it('shows a retryable service error when the API is unavailable', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 503 } })
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(wrapper.get('[role="alert"]').text()).toBe(
      '現在ログインできません。通信環境を確認し、しばらくしてからもう一度お試しください。',
    )
    expect((wrapper.get('#admin-password').element as HTMLInputElement).value).toBe('')
  })

  it('shows a rate-limit message after too many attempts', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 429 } })
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(wrapper.get('[role="alert"]').text()).toBe(
      '試行回数が多すぎます。しばらくしてからもう一度お試しください。',
    )
  })
})
