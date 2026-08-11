<template>
    <button
        class="ui-button"
        :class="[
            `ui-button--${variant}`,
            `ui-button--${size}`,
            {
                'ui-button--loading': loading,
                'ui-button--block': block,
            },
        ]"
        :type="type"
        :disabled="disabled || loading"
        data-testid="ui-button"
    >
        <ui-spinner v-if="loading" class="ui-button__spinner" />
        <span v-if="$slots['icon-left'] && !loading" class="ui-button__icon">
            <slot name="icon-left" />
        </span>
        <span class="ui-button__label">
            <slot />
        </span>
        <span v-if="$slots['icon-right'] && !loading" class="ui-button__icon">
            <slot name="icon-right" />
        </span>
    </button>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        variant?: 'primary' | 'brand' | 'secondary' | 'ghost' | 'danger'
        size?: 'sm' | 'md' | 'lg'
        loading?: boolean
        disabled?: boolean
        block?: boolean
        type?: 'button' | 'submit'
    }>(),
    {
        variant: 'primary',
        size: 'md',
        loading: false,
        disabled: false,
        block: false,
        type: 'button',
    },
)
</script>

<style scoped lang="scss">
.ui-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    min-height: var(--control-h-md);
    padding-inline: var(--space-4);
    border: 1px solid transparent;
    border-radius: var(--radius-pill);
    font-family: var(--font-body);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    line-height: 1;
    cursor: pointer;
    transition:
        background-color var(--transition-fast),
        color var(--transition-fast),
        border-color var(--transition-fast);
}

.ui-button--sm {
    min-height: var(--control-h-sm);
    padding-inline: var(--space-3);
}

.ui-button--lg {
    min-height: var(--control-h-lg);
}

.ui-button--block {
    width: 100%;
}

.ui-button--primary {
    background: var(--color-primary);
    color: var(--color-surface);
}

.ui-button--primary:hover:not(:disabled) {
    background: var(--color-primary-hover);
}

.ui-button--brand {
    background: var(--color-brand);
    color: var(--color-surface);
}

.ui-button--brand:hover:not(:disabled) {
    background: var(--color-brand-hover);
}

.ui-button--secondary {
    background: var(--color-surface);
    border-color: var(--color-border-strong);
    color: var(--color-text);
}

.ui-button--secondary:hover:not(:disabled) {
    background: var(--color-surface-muted);
}

.ui-button--ghost {
    background: transparent;
    color: var(--color-link);
}

.ui-button--ghost:hover:not(:disabled) {
    color: var(--color-link-hover);
}

.ui-button--danger {
    background: var(--color-danger);
    color: var(--color-surface);
}

.ui-button--danger:hover:not(:disabled) {
    background: var(--color-danger-strong);
}

.ui-button:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.ui-button--loading {
    pointer-events: none;
}

.ui-button__spinner {
    width: 13px;
    height: 13px;
}
</style>
