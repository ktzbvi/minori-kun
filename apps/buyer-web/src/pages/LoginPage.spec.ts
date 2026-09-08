import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  csrf: vi.fn(),
  login: vi.fn(),
  invalidateQueries: vi.fn(),
  replace: vi.fn(),
  back: vi.fn(),
}))

vi.mock('@/lib/api', () => ({ authApi: { csrf: mocks.csrf, login: mocks.login } }))
vi.mock('@/lib/query', () => ({ queryClient: { invalidateQueries: mocks.invalidateQueries } }))
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
    await wrapper.get('#buyer-email').setValue('buyer@example.test')
    await wrapper.get('#buyer-password').setValue('secret-password')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(mocks.csrf).toHaveBeenCalledOnce()
    expect(mocks.login).toHaveBeenCalledWith('buyer@example.test', 'secret-password')
    expect(mocks.invalidateQueries).toHaveBeenCalledWith({ queryKey: ['current-session'] })
    expect(mocks.replace).toHaveBeenCalledWith('/')
  })

  it('shows validation guidance when credentials have not been entered', async () => {
    const wrapper = mountPage()
    await wrapper.get('form').trigger('submit')

    expect(wrapper.get('[role="alert"]').text()).toBe(
      'メールアドレスとパスワードを入力してください。',
    )
    expect(mocks.login).not.toHaveBeenCalled()
  })

  it('clears the password and shows a neutral error after a failed login', async () => {
    mocks.login.mockRejectedValueOnce({ isAxiosError: true, response: { status: 422 } })
    const wrapper = mountPage()
    await wrapper.get('#buyer-email').setValue('buyer@example.test')
    await wrapper.get('#buyer-password').setValue('wrong-password')
    await wrapper.get('form').trigger('submit')
    await flushPromises()

    expect(wrapper.get('[role="alert"]').text()).toBe(
      'メールアドレスまたはパスワードを確認してください。',
    )
    expect((wrapper.get('#buyer-password').element as HTMLInputElement).value).toBe('')
  })
})
