import { defineStore } from 'pinia'

export const usePresentationStore = defineStore('presentation', {
  state: () => ({ navigationOpen: false, homeCategory: 'all' }),
})
