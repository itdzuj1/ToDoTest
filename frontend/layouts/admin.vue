<template>
    <div class="layout-admin" data-testid="layout-admin">
        <aside class="layout-admin__sidebar" aria-label="Admin navigation">
            <nuxt-link to="/admin" class="layout-admin__brand">
                <span class="layout-admin__brand-mark" aria-hidden="true">T</span>
                <span class="layout-admin__brand-name">ToDoTest Admin</span>
            </nuxt-link>
        </aside>
        <div class="layout-admin__body">
            <header class="layout-admin__header">
                <p class="layout-admin__eyebrow">Admin</p>
            </header>
            <main class="layout-admin__main">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped lang="scss">
.layout-admin {
    --row-h: var(--row-h-compact);
    --control-h: var(--control-h-sm);
    display: grid;
    grid-template-columns: var(--sidebar-w) minmax(0, 1fr);
    min-height: 100vh;
    background: var(--color-surface);
}

.layout-admin__sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--color-sidebar-bg);
    color: var(--color-surface);
}

.layout-admin__brand {
    display: inline-flex;
    align-items: center;
    gap: var(--space-3);
    color: inherit;
    text-decoration: none;
}

.layout-admin__brand-mark {
    display: inline-grid;
    place-items: center;
    width: var(--avatar-sm);
    height: var(--avatar-sm);
    border-radius: 7px;
    background: var(--color-brand);
    font-family: var(--font-display);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-heading-strong);
}

.layout-admin__brand-name {
    font-family: var(--font-display);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-semibold);
}

.layout-admin__body {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.layout-admin__header {
    display: flex;
    align-items: center;
    height: var(--header-h);
    padding-inline: var(--space-6);
    border-bottom: 1px solid var(--color-border);
}

.layout-admin__eyebrow {
    margin: 0;
    color: var(--color-text-subtle);
    font-family: var(--font-mono);
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.layout-admin__main {
    padding: var(--space-6);
}
</style>
