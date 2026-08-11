import { mountSuspended } from '@nuxt/test-utils/runtime'
import { describe, expect, it } from 'vitest'

import UiButton from '../../../components/ui/UiButton.vue'

describe('UiButton', () => {
    it('disables interaction while loading', async () => {
        const wrapper = await mountSuspended(UiButton, {
            props: {
                loading: true,
            },
            slots: {
                default: 'Сохранить',
            },
        })

        const button = wrapper.get('button')

        expect(button.attributes('disabled')).toBeDefined()
        expect(wrapper.find('[data-testid="ui-spinner"]').exists()).toBe(true)
    })
})
