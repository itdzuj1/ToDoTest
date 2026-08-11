<template>
    <div class="ui-empty" data-testid="ui-empty">
        <h2 class="ui-empty__title">{{ title }}</h2>
        <p v-if="description" class="ui-empty__description">{{ description }}</p>
        <ui-button v-if="actionLabel" variant="secondary" data-testid="ui-empty-action" @click="$emit('action')">
            {{ actionLabel }}
        </ui-button>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    title: string
    description?: string
    actionLabel?: string
}>()

defineEmits<{
    action: []
}>()
</script>

<style scoped lang="scss">
.ui-empty {
    display: grid;
    gap: var(--space-3);
    justify-items: start;
    width: 100%;
    padding: var(--space-7);
}

.ui-empty__title {
    margin: 0;
    font-family: var(--font-display);
    font-size: var(--fs-subheading);
    font-weight: var(--fw-heading);
    letter-spacing: var(--ls-tight);
}

.ui-empty__description {
    margin: 0;
    color: var(--color-text-muted);
    font-size: var(--fs-body-sm);
}
</style>
