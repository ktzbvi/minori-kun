import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  csrf: vi.fn(),
  login: vi.fn(),
  invalidateQueries: vi.fn(),
  replace: vi.fn(),
  back: vi.fn(),
  toastSuccess: vi.fn(),
}))

vi.mock('@/lib/api', () => ({ authApi: { csrf: mocks.csrf, login: mocks.login } }))
vi.mock('@/lib/query', () => ({ queryClient: { invalidateQueries: mocks.invalidateQueries } }))
vi.mock('vue-sonner', () => ({
  Toaster: { template: '<div />' },
  toast: { success: mocks.toastSuccess },
}))
vi.mock('vue-router', () => ({
  RouterLink: { props: ['to'], template: '<a :href="to"><slot /></a>' },
  useRoute: () => ({ query: { redirect: '/' } }),
  useRouter: () => ({ replace: mocks.replace, back: mocks.back }),
}))

import LoginPage from './LoginPage.vue'

function mountPage() {
  return mount(LoginPage)
}

describe('Buyer Login', () => {
  beforeEach(() => vi.clearAllMocks())

  it('shows the required fields and supporting navigation', () => {
    const wrapper = mountPage()

    expect(wrapper.text()).toContain('ログイン')
    expect(wrapper.text()).toContain('新規会員登録')
    expect(wrapper.text()).toContain('パスワードをお忘れですか？')
    expect(wrapper.find('a[href="/register"]').exists()).toBe(true)
    expect(wrapper.find('a[href="/password-reset"]').exists()).toBe(true)
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeUndefined()
  })

  it('uses the Buyer Sanctum flow and returns to the intended destination', async () => {
    const wrapper = mountPage()
    await wrapper.get('#buyer-email').setValue('  buyer@example.test  ')
    await wrapper.get('#buyer-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(mocks.toastSuccess).toHaveBeenCalledWith('成功'))

    expect(mocks.csrf).toHaveBeenCalledOnce()
    expect(mocks.login).toHaveBeenCalledWith('buyer@example.test', 'secret-password')
    expect(mocks.invalidateQueries).toHaveBeenCalledWith({ queryKey: ['current-session'] })
    expect(mocks.replace).toHaveBeenCalledWith('/')
    expect(mocks.toastSuccess).toHaveBeenCalledWith('成功')
    expect(mocks.replace.mock.invocationCallOrder[0]!).toBeLessThan(
      mocks.toastSuccess.mock.invocationCallOrder[0]!,
    )
  })

  it('shows field-level validation guidance when credentials have not been entered', async () => {
    const wrapper = mountPage()
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('#buyer-email-error').exists()).toBe(true))

    expect(wrapper.get('#buyer-email-error').text()).toBe('メールアドレスを入力してください。')
    expect(wrapper.get('#buyer-password-error').text()).toBe('パスワードを入力してください。')
    expect(wrapper.get('#buyer-email').attributes('aria-invalid')).toBe('true')
    expect(wrapper.get('#buyer-password').attributes('aria-invalid')).toBe('true')
    expect(mocks.login).not.toHaveBeenCalled()
  })

  it('validates the email format and clears the error while the field is corrected', async () => {
    const wrapper = mountPage()
    await wrapper.get('#buyer-email').setValue('not-an-email')
    await wrapper.get('#buyer-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('#buyer-email-error').exists()).toBe(true))

    expect(wrapper.get('#buyer-email-error').text()).toBe(
      '正しいメールアドレスを入力してください。',
    )
    expect(mocks.login).not.toHaveBeenCalled()

    await wrapper.get('#buyer-email').setValue('buyer@example.test')
    await vi.waitFor(() => expect(wrapper.find('#buyer-email-error').exists()).toBe(false))

    expect(wrapper.find('#buyer-email-error').exists()).toBe(false)
    expect(wrapper.get('#buyer-email').attributes('aria-invalid')).toBe('false')
  })

  it('disables submission only while the login request is running', async () => {
    let resolveLogin!: () => void
    mocks.login.mockImplementationOnce(
      () =>
        new Promise<void>((resolve) => {
          resolveLogin = resolve
        }),
    )
    const wrapper = mountPage()
    await wrapper.get('#buyer-email').setValue('buyer@example.test')
    await wrapper.get('#buyer-password').setValue('secret-password')

    void wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(mocks.login).toHaveBeenCalledOnce())
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeDefined()

    resolveLogin()
    await flushPromises()
    expect(wrapper.get('button[type="submit"]').attributes('disabled')).toBeUndefined()
  })

  it('clears the password and shows a neutral error after a failed login', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 422 } })
    const wrapper = mountPage()
    await wrapper.get('#buyer-email').setValue('buyer@example.test')
    await wrapper.get('#buyer-password').setValue('wrong-password')
    await wrapper.get('form').trigger('submit')
    await vi.waitFor(() => expect(wrapper.find('[role="alert"]').exists()).toBe(true))

    expect(wrapper.get('[role="alert"]').text()).toBe(
      'メールアドレスまたはパスワードを確認してください。',
    )
    expect((wrapper.get('#buyer-password').element as HTMLInputElement).value).toBe('')
    expect(mocks.toastSuccess).not.toHaveBeenCalled()
  })
})
