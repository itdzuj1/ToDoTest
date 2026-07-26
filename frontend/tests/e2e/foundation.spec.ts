import { expect, test } from '@playwright/test'

test('shows the project foundation', async ({ page }) => {
    await page.goto('/')

    await expect(page.getByRole('heading', { name: 'ToDoTest' })).toBeVisible()
})
