import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  csrf: vi.fn(),
  login: vi.fn(),
  invalidateQueries: vi.fn(),
  replace: vi.fn(),
  toastSuccess: vi.fn(),
}))

vi.mock('@/lib/api', () => ({ authApi: { csrf: mocks.csrf, login: mocks.login } }))
vi.mock('@/lib/query', () => ({ queryClient: { invalidateQueries: mocks.invalidateQueries } }))
vi.mock('vue-sonner', () => ({
  Toaster: { template: '<div />' },
  toast: { success: mocks.toastSuccess },
}))
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
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeUndefined()
  })

  it('shows field-level validation guidance for an invalid submission', async () => {
    const wrapper = mount(LoginPage)
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('#admin-email-error').exists()).toBe(true))

    expect(wrapper.get('#admin-email-error').text()).toBe('メールアドレスを入力してください。')
    expect(wrapper.get('#admin-password-error').text()).toBe('パスワードを入力してください。')
    expect(wrapper.get('#admin-email').attributes('aria-invalid')).toBe('true')
    expect(wrapper.get('#admin-password').attributes('aria-invalid')).toBe('true')
    expect(mocks.login).not.toHaveBeenCalled()
  })

  it('validates the email format and clears the error while the field is corrected', async () => {
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('not-an-email')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('#admin-email-error').exists()).toBe(true))

    expect(wrapper.get('#admin-email-error').text()).toBe(
      '正しいメールアドレスを入力してください。',
    )
    expect(mocks.login).not.toHaveBeenCalled()

    await wrapper.get('#admin-email').setValue('admin@example.test')
    await vi.waitFor(() => expect(wrapper.find('#admin-email-error').exists()).toBe(false))

    expect(wrapper.find('#admin-email-error').exists()).toBe(false)
    expect(wrapper.get('#admin-email').attributes('aria-invalid')).toBe('false')
  })

  it('uses the existing Sanctum authentication flow and opens the dashboard', async () => {
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('  admin@example.test  ')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(mocks.toastSuccess).toHaveBeenCalledWith('成功'))

    expect(mocks.csrf).toHaveBeenCalledOnce()
    expect(mocks.login).toHaveBeenCalledWith('admin@example.test', 'secret-password')
    expect(mocks.invalidateQueries).toHaveBeenCalledWith({ queryKey: ['current-session'] })
    expect(mocks.replace).toHaveBeenCalledWith('/')
    expect(mocks.toastSuccess).toHaveBeenCalledWith('成功')
    expect(mocks.replace.mock.invocationCallOrder[0]).toBeLessThan(
      mocks.toastSuccess.mock.invocationCallOrder[0],
    )
  })

  it('disables submission only while the login request is running', async () => {
    let resolveLogin!: () => void
    mocks.login.mockImplementationOnce(
      () =>
        new Promise<void>((resolve) => {
          resolveLogin = resolve
        }),
    )
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('secret-password')

    void wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(mocks.login).toHaveBeenCalledOnce())
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeDefined()

    resolveLogin()
    await flushPromises()
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeUndefined()
  })

  it('shows a neutral error and clears the password after authentication fails', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 422 } })
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('wrong-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('[role="alert"]').exists()).toBe(true))

    expect(wrapper.get('[role="alert"]').text()).toBe(
      'メールアドレスまたはパスワードを確認してください。',
    )
    expect((wrapper.get('#admin-password').element as HTMLInputElement).value).toBe('')
    expect(mocks.toastSuccess).not.toHaveBeenCalled()
  })

  it('shows a retryable service error when the API is unavailable', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 503 } })
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('[role="alert"]').exists()).toBe(true))

    expect(wrapper.get('[role="alert"]').text()).toBe(
      '現在ログインできません。通信環境を確認し、しばらくしてからもう一度お試しください。',
    )
    expect((wrapper.get('#admin-password').element as HTMLInputElement).value).toBe('')
    expect(mocks.toastSuccess).not.toHaveBeenCalled()
  })

  it('shows a rate-limit message after too many attempts', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 429 } })
    const wrapper = mount(LoginPage)
    await wrapper.get('#admin-email').setValue('admin@example.test')
    await wrapper.get('#admin-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('[role="alert"]').exists()).toBe(true))

    expect(wrapper.get('[role="alert"]').text()).toBe(
      '試行回数が多すぎます。しばらくしてからもう一度お試しください。',
    )
    expect(mocks.toastSuccess).not.toHaveBeenCalled()
  })
})
