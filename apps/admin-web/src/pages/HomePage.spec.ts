import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import HomePage from './HomePage.vue'

describe('Admin Dashboard', () => {
  it('shows every required operational summary area', () => {
    const wrapper = mount(HomePage, {
      global: {
        stubs: {
          AdminAppLayout: { template: '<div><slot /></div>' },
        },
      },
    })

    expect(wrapper.text()).toContain('生産者状況')
    expect(wrapper.text()).toContain('商品・注文注意')
    expect(wrapper.text()).toContain('売上・会社手数料')
    expect(wrapper.text()).toContain('振込状況')
    expect(wrapper.text()).toContain('重要な通知')
  })
})
