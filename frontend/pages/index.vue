<template>
    <section class="foundation" data-testid="foundation-page">
        <h1>ToDoTest</h1>
        <p>Основа приложения готова. Пользовательские сценарии будут добавлены следующими этапами.</p>
        <ui-button data-testid="foundation-cta" variant="brand">Продолжить</ui-button>
    </section>
</template>

<script setup lang="ts">
definePageMeta({
    layout: 'default',
})
</script>

<style scoped lang="scss">
.foundation {
    display: grid;
    gap: var(--space-4);
    max-width: 36rem;
}

.foundation h1 {
    margin: 0;
    color: var(--color-text-strong);
    font-family: var(--font-display);
    font-size: var(--fs-heading);
    font-weight: var(--fw-display);
    letter-spacing: var(--ls-display);
    line-height: var(--lh-heading);
}

.foundation p {
    margin: 0;
    color: var(--color-text-muted);
    font-size: var(--fs-body);
}
</style>
