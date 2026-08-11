<template>
    <span class="ui-spinner" :class="`ui-spinner--${size}`" role="status" aria-live="polite" data-testid="ui-spinner">
        <span class="ui-spinner__label">{{ label }}</span>
    </span>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        size?: 'sm' | 'md'
        label?: string
    }>(),
    {
        size: 'sm',
        label: 'Загрузка',
    },
)
</script>

<style scoped lang="scss">
.ui-spinner {
    position: relative;
    display: inline-block;
    border: 2px solid var(--color-border-strong);
    border-top-color: var(--color-primary);
    border-radius: var(--radius-pill);
    animation: ui-spinner-rotate 0.7s linear infinite;
}

.ui-spinner--sm {
    width: 13px;
    height: 13px;
}

.ui-spinner--md {
    width: 20px;
    height: 20px;
}

.ui-spinner__label {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

@keyframes ui-spinner-rotate {
    to {
        transform: rotate(360deg);
    }
}
</style>
