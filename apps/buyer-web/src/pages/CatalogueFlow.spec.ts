import { mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  push: vi.fn(),
  replace: vi.fn(),
  back: vi.fn(),
  success: vi.fn(),
  warning: vi.fn(),
  route: {
    params: {} as Record<string, string>,
    query: {} as Record<string, string>,
  },
}))

vi.mock('vue-router', () => ({
  useRoute: () => mocks.route,
  useRouter: () => ({
    push: mocks.push,
    replace: mocks.replace,
    back: mocks.back,
  }),
}))

vi.mock('@minorikun/ui', () => ({
  toast: {
    success: mocks.success,
    warning: mocks.warning,
  },
}))

import BuyerBottomNavigation from '@/components/BuyerBottomNavigation.vue'
import { useCart } from '@/lib/cart'
import CategoryPage from './CategoryPage.vue'
import CategoryProductListPage from './CategoryProductListPage.vue'
import HomePage from './HomePage.vue'
import ProductDetailPage from './ProductDetailPage.vue'
import SearchPage from './SearchPage.vue'

const tomato = '\u30c8\u30de\u30c8'
const vegetables = '\u91ce\u83dc'
const seasonalSet = '\u5b63\u7bc0\u306e\u91ce\u83dc\u30bb\u30c3\u30c8'
const tomatoAssortment = '\u30c8\u30de\u30c8\u8a70\u3081\u5408\u308f\u305b'
const minamisakiTomato = '\u5357\u5d0e\u30c8\u30de\u30c8'

function buttonWithText(wrapper: ReturnType<typeof mount>, text: string) {
  const button = wrapper.findAll('button').find((candidate) => candidate.text().includes(text))

  if (!button) throw new Error('Button containing "' + text + '" was not found.')

  return button
}

describe('Buyer catalogue flows', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mocks.route.params = {}
    mocks.route.query = {}
    useCart().reset()
  })

  it('filters Home products by category and opens Search', async () => {
    const wrapper = mount(HomePage)

    await buttonWithText(wrapper, vegetables).trigger('click')

    expect(wrapper.text()).toContain(minamisakiTomato)
    expect(wrapper.text()).toContain(tomatoAssortment)
    expect(wrapper.text()).not.toContain('\u65b0\u7c73 5kg')

    await wrapper.get('[aria-label="Search"]').trigger('click')
    expect(mocks.push).toHaveBeenCalledWith({ name: 'search' })
  })

  it('adds a Home product to the shared cart and opens its detail page', async () => {
    const wrapper = mount(HomePage)

    await buttonWithText(wrapper, '\u30ab\u30fc\u30c8\u306b\u8ffd\u52a0').trigger('click')
    expect(useCart().cartItemCount.value).toBe(1)
    expect(mocks.success).toHaveBeenCalledOnce()

    await buttonWithText(wrapper, seasonalSet).trigger('click')
    expect(mocks.push).toHaveBeenCalledWith({
      name: 'product-detail',
      params: { productId: 'seasonal-vegetable-set' },
    })
  })

  it('opens the selected category product list from B02', async () => {
    const wrapper = mount(CategoryPage)

    await buttonWithText(wrapper, vegetables).trigger('click')

    expect(mocks.push).toHaveBeenCalledWith({
      name: 'category-products',
      params: { categoryId: 'vegetables' },
    })
  })

  it('shows only selected-category products in B03 and returns to B02', async () => {
    mocks.route.params = { categoryId: 'vegetables' }
    const wrapper = mount(CategoryProductListPage)

    expect(wrapper.text()).toContain(minamisakiTomato)
    expect(wrapper.text()).toContain(tomatoAssortment)
    expect(wrapper.text()).not.toContain('\u65b0\u7c73 5kg')

    await wrapper.get('[aria-label="Back"]').trigger('click')
    expect(mocks.push).toHaveBeenCalledWith({ name: 'categories' })
  })

  it('uses prefix matching against a product name or its search terms in B04', () => {
    mocks.route.query = { q: tomato }
    const wrapper = mount(SearchPage)

    expect(wrapper.text()).toContain(minamisakiTomato)
    expect(wrapper.text()).toContain(tomatoAssortment)
  })

  it('shows the no-results state in B04', () => {
    mocks.route.query = { q: '\u3076\u3069\u3046' }
    const wrapper = mount(SearchPage)

    expect(wrapper.text()).toContain(
      '\u8a72\u5f53\u3059\u308b\u5546\u54c1\u304c\u3042\u308a\u307e\u305b\u3093',
    )
  })

  it('updates the selected variant and adds the selected quantity to the shared cart in B05', async () => {
    mocks.route.params = { productId: 'seasonal-vegetable-set' }
    const wrapper = mount(ProductDetailPage)

    await buttonWithText(wrapper, '\u5927\u5bb9\u91cf').trigger('click')
    expect(wrapper.text()).toContain('\u7a0e\u8fbc 3,680\u5186')

    await wrapper.get('[aria-label="Increase quantity"]').trigger('click')
    await buttonWithText(wrapper, '\u30ab\u30fc\u30c8\u306b\u8ffd\u52a0').trigger('click')

    expect(useCart().cartItemCount.value).toBe(2)
    expect(mocks.success).toHaveBeenCalledOnce()
  })

  it('routes Home and Category from the shared bottom navigation', async () => {
    const wrapper = mount(BuyerBottomNavigation)

    await buttonWithText(wrapper, '\u30db\u30fc\u30e0').trigger('click')
    await buttonWithText(wrapper, '\u30ab\u30c6\u30b4\u30ea').trigger('click')

    expect(mocks.push).toHaveBeenNthCalledWith(1, { name: 'home' })
    expect(mocks.push).toHaveBeenNthCalledWith(2, { name: 'categories' })
  })

  it('routes to Cart and gives feedback for the unfinished My Page', async () => {
    const wrapper = mount(BuyerBottomNavigation)

    await buttonWithText(wrapper, '\u30ab\u30fc\u30c8').trigger('click')
    await buttonWithText(wrapper, '\u30de\u30a4\u30da\u30fc\u30b8').trigger('click')

    expect(mocks.push).toHaveBeenCalledWith({ name: 'cart' })
    expect(mocks.warning).toHaveBeenCalledOnce()
  })
})
