<template>
    <div class="ui-error" role="alert" data-testid="ui-error">
        <h2 class="ui-error__title">{{ title }}</h2>
        <p v-if="description" class="ui-error__description">{{ description }}</p>
        <p v-if="code !== undefined" class="ui-error__code">Код: {{ code }}</p>
        <ui-button v-if="retryable" variant="secondary" data-testid="ui-error-retry" @click="$emit('retry')">
            Повторить
        </ui-button>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string
        description?: string
        code?: string | number
        retryable?: boolean
    }>(),
    {
        description: undefined,
        code: undefined,
        retryable: true,
    },
)

defineEmits<{
    retry: []
}>()
</script>

<style scoped lang="scss">
.ui-error {
    display: grid;
    gap: var(--space-3);
    justify-items: start;
    width: 100%;
    padding: var(--space-7);
    border: 1px solid var(--color-danger);
    border-radius: var(--radius-card);
    background: var(--color-danger-tint);
}

.ui-error__title {
    margin: 0;
    color: var(--color-danger-strong);
    font-family: var(--font-display);
    font-size: var(--fs-subheading);
    font-weight: var(--fw-heading);
}

.ui-error__description,
.ui-error__code {
    margin: 0;
    color: var(--color-danger-strong);
    font-size: var(--fs-body-sm);
}

.ui-error__code {
    font-family: var(--font-mono);
}
</style>
