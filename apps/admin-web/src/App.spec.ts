import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import App from './App.vue'

describe('App', () => {
  it('mounts the application shell and shared toaster', () => {
    const wrapper = mount(App, {
      global: { stubs: { RouterView: true } },
    })

    const toaster = wrapper.get('[data-sonner-toaster]')
    expect(toaster.attributes('data-y-position')).toBe('bottom')
    expect(toaster.attributes('data-x-position')).toBe('right')
  })
})
