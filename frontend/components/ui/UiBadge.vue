<template>
    <span class="ui-badge" :class="[`ui-badge--${tone}`, `ui-badge--${size}`]" data-testid="ui-badge">
        <slot />
    </span>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        tone?: 'neutral' | 'success' | 'info' | 'warning' | 'danger' | 'brand'
        size?: 'sm' | 'md'
    }>(),
    {
        tone: 'neutral',
        size: 'md',
    },
)
</script>

<style scoped lang="scss">
.ui-badge {
    display: inline-flex;
    align-items: center;
    min-height: 24px;
    padding-inline: var(--space-2);
    border-radius: var(--radius-pill);
    font-size: 12px;
    font-weight: var(--fw-medium);
    line-height: 1;
    white-space: nowrap;
}

.ui-badge--sm {
    min-height: 22px;
}

.ui-badge--neutral {
    background: var(--color-surface-subtle);
    color: var(--color-text-muted);
}

.ui-badge--success {
    background: var(--color-success-tint);
    color: var(--color-success-strong);
}

.ui-badge--info {
    background: var(--color-info-tint);
    color: var(--color-info-strong);
}

.ui-badge--warning {
    background: var(--color-warning-tint);
    color: var(--color-warning-strong);
}

.ui-badge--danger {
    background: var(--color-danger-tint);
    color: var(--color-danger-strong);
}

.ui-badge--brand {
    background: var(--color-brand);
    color: var(--color-surface);
}
</style>
