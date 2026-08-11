<template>
    <div class="layout-default" data-testid="layout-default">
        <header class="layout-default__header">
            <nuxt-link to="/" class="layout-default__brand" data-testid="public-brand">
                <span class="layout-default__brand-mark" aria-hidden="true">T</span>
                <span class="layout-default__brand-name">ToDoTest</span>
            </nuxt-link>
        </header>
        <main class="layout-default__main">
            <slot />
        </main>
    </div>
</template>

<style scoped lang="scss">
.layout-default {
    --row-h: var(--row-h);
    --control-h: var(--control-h-md);
    min-height: 100vh;
    background: var(--color-surface);
}

.layout-default__header {
    display: flex;
    align-items: center;
    height: var(--header-h);
    padding-inline: var(--space-8);
    border-bottom: 1px solid var(--color-border);
}

.layout-default__brand {
    display: inline-flex;
    align-items: center;
    gap: var(--space-3);
    color: var(--color-text-strong);
    text-decoration: none;
}

.layout-default__brand-mark {
    display: inline-grid;
    place-items: center;
    width: var(--avatar-sm);
    height: var(--avatar-sm);
    border-radius: 7px;
    background: var(--color-primary);
    color: var(--color-surface);
    font-family: var(--font-display);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-heading-strong);
}

.layout-default__brand-name {
    font-family: var(--font-display);
    font-size: var(--fs-body);
    font-weight: var(--fw-heading);
    letter-spacing: var(--ls-tight);
}

.layout-default__main {
    width: min(100% - (2 * var(--space-8)), var(--content-max));
    margin-inline: auto;
    padding-block: var(--space-12);
}
</style>
