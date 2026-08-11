import { mountSuspended } from '@nuxt/test-utils/runtime'
import { describe, expect, it } from 'vitest'

import IndexPage from '../../pages/index.vue'

describe('Foundation page', () => {
    it('renders the project foundation heading', async () => {
        const wrapper = await mountSuspended(IndexPage)

        expect(wrapper.get('h1').text()).toBe('ToDoTest')
        expect(wrapper.get('[data-testid="foundation-cta"]').text()).toContain('Продолжить')
    })
})
