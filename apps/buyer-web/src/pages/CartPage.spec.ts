import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  back: vi.fn(),
  warning: vi.fn(),
  push: vi.fn(),
  error: vi.fn(),
}))

vi.mock('vue-router', () => ({
  useRouter: () => ({
    back: mocks.back,
    push: mocks.push,
  }),
}))

vi.mock('@minorikun/ui', () => ({
  toast: {
    warning: mocks.warning,
    error: mocks.error,
  },
}))

import { useCart } from '@/lib/cart'
import CartPage from './CartPage.vue'

const seasonalSet = '\u5b63\u7bc0\u306e\u91ce\u83dc\u30bb\u30c3\u30c8'
const minamisakiTomato = '\u5357\u5d0e\u30c8\u30de\u30c8'

function buttonWithText(wrapper: ReturnType<typeof mount>, text: string) {
  const button = wrapper.findAll('button').find((candidate) => candidate.text().includes(text))

  if (!button) throw new Error('Button containing "' + text + '" was not found.')

  return button
}

describe('CartPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    useCart().reset()
  })

  it('returns an empty cart customer to Home', async () => {
    const wrapper = mount(CartPage)

    expect(wrapper.text()).toContain(
      '\u30ab\u30fc\u30c8\u306b\u5546\u54c1\u304c\u3042\u308a\u307e\u305b\u3093',
    )

    await buttonWithText(wrapper, '\u5546\u54c1\u4e00\u89a7\u3078\u623b\u308b').trigger('click')

    expect(mocks.push).toHaveBeenCalledWith({ name: 'home' })
  })

  it('groups cart items, updates quantities, and recalculates totals', async () => {
    const cart = useCart()
    cart.addItem('seasonal-vegetable-set', 2, 'regular')
    cart.addItem('minamisaki-tomato', 1, 'standard')

    const wrapper = mount(CartPage)

    expect(wrapper.text()).toContain(seasonalSet)
    expect(wrapper.text()).toContain(minamisakiTomato)
    expect(wrapper.text()).toContain('\u7a0e\u8fbc 6,644\u5186')

    await wrapper.get('[aria-label="Decrease quantity"]').trigger('click')

    expect(cart.cartItemCount.value).toBe(2)
    expect(wrapper.text()).toContain('\u7a0e\u8fbc 3,962\u5186')

    await wrapper.get('[aria-label="Increase quantity"]').trigger('click')

    expect(cart.cartItemCount.value).toBe(3)
    expect(wrapper.text()).toContain('\u7a0e\u8fbc 6,644\u5186')
  })

  it('removes an item and opens order confirmation', async () => {
    const cart = useCart()
    cart.addItem('seasonal-vegetable-set', 1, 'regular')

    const wrapper = mount(CartPage)

    await wrapper.get(`[aria-label="${seasonalSet}\u3092\u524a\u9664"]`).trigger('click')

    expect(cart.cartItemCount.value).toBe(0)
    expect(mocks.error).toHaveBeenCalledOnce()
    expect(wrapper.text()).toContain(
      '\u30ab\u30fc\u30c8\u306b\u5546\u54c1\u304c\u3042\u308a\u307e\u305b\u3093',
    )

    cart.addItem('seasonal-vegetable-set', 1, 'regular')
    await wrapper.vm.$nextTick()

    await buttonWithText(wrapper, '\u6ce8\u6587\u5185\u5bb9\u3092\u78ba\u8a8d\u3059\u308b').trigger(
      'click',
    )

    expect(mocks.push).toHaveBeenLastCalledWith({ name: 'order-confirmation' })
  })
})
