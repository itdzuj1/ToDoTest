<template>
    <main class="app-shell">
        <nuxt-route-announcer />
        <h1>ToDoTest</h1>
        <p>Основа приложения готова. Пользовательские сценарии будут добавлены следующими этапами.</p>
    </main>
</template>

<style scoped lang="scss">
.app-shell {
    width: min(100% - (2 * var(--space-4)), var(--content-max));
    margin-inline: auto;
    padding-block: var(--space-12);
}
</style>
