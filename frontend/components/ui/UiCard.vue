<template>
    <article class="ui-card" data-testid="ui-card">
        <header v-if="$slots.header" class="ui-card__header">
            <slot name="header" />
        </header>
        <div class="ui-card__body">
            <slot />
        </div>
        <footer v-if="$slots.footer" class="ui-card__footer">
            <slot name="footer" />
        </footer>
    </article>
</template>

<style scoped lang="scss">
.ui-card {
    display: grid;
    gap: var(--space-3);
    padding: var(--space-7);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-card);
    background: var(--color-surface);
}

.ui-card__header,
.ui-card__footer,
.ui-card__body {
    min-width: 0;
}
</style>
