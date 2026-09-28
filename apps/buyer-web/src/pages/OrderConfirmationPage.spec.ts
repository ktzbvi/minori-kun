import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  back: vi.fn(),
  warning: vi.fn(),
}))

vi.mock('vue-router', () => ({
  useRouter: () => ({
    back: mocks.back,
  }),
}))

vi.mock('@minorikun/ui', () => ({
  toast: { warning: mocks.warning },
}))

import { useCart } from '@/lib/cart'
import OrderConfirmationPage from './OrderConfirmationPage.vue'

const seasonalSet = '\u5b63\u7bc0\u306e\u91ce\u83dc\u30bb\u30c3\u30c8'
const minamisakiTomato = '\u5357\u5d0e\u30c8\u30de\u30c8'

function buttonWithText(wrapper: ReturnType<typeof mount>, text: string) {
  const button = wrapper.findAll('button').find((candidate) => candidate.text().includes(text))

  if (!button) throw new Error('Button containing "' + text + '" was not found.')

  return button
}

describe('OrderConfirmationPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    useCart().reset()
  })

  it('shows the address, grouped order lines, and calculated total', () => {
    const cart = useCart()
    cart.addItem('seasonal-vegetable-set', 1, 'regular')
    cart.addItem('minamisaki-tomato', 2, 'standard')

    const wrapper = mount(OrderConfirmationPage)

    expect(wrapper.text()).toContain('\u7530\u4e2d \u592a\u90ce')
    expect(wrapper.text()).toContain(seasonalSet)
    expect(wrapper.text()).toContain(minamisakiTomato)
    expect(wrapper.text()).toContain('5,242\u5186')
  })

  it('keeps address editing and payment under development', async () => {
    const wrapper = mount(OrderConfirmationPage)

    await buttonWithText(wrapper, '\u5909\u66f4').trigger('click')
    await buttonWithText(wrapper, '\u6c7a\u6e08\u753b\u9762\u3078\u9032\u3080').trigger('click')

    expect(mocks.warning).toHaveBeenCalledTimes(2)
  })
})
