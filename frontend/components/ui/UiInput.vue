<template>
    <input
        class="ui-input"
        :class="[
            `ui-input--${size}`,
            {
                'ui-input--invalid': invalid,
            },
        ]"
        :value="modelValue ?? ''"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :aria-invalid="invalid || undefined"
        data-testid="ui-input"
        @input="onInput"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
    />
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        modelValue: string | number | null
        placeholder?: string
        invalid?: boolean
        disabled?: boolean
        readonly?: boolean
        size?: 'sm' | 'md'
    }>(),
    {
        placeholder: undefined,
        invalid: false,
        disabled: false,
        readonly: false,
        size: 'md',
    },
)

const emit = defineEmits<{
    'update:modelValue': [value: string]
    blur: [event: FocusEvent]
    focus: [event: FocusEvent]
}>()

function onInput(event: Event): void {
    const target = event.target

    if (!(target instanceof HTMLInputElement)) {
        return
    }

    emit('update:modelValue', target.value)
}
</script>

<style scoped lang="scss">
.ui-input {
    width: 100%;
    min-height: var(--control-h-md);
    padding-inline: var(--space-3);
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-input);
    background: var(--color-surface);
    color: var(--color-text);
}

.ui-input--sm {
    min-height: var(--control-h-sm);
}

.ui-input--invalid {
    border-color: var(--color-danger);
}

.ui-input:disabled {
    background: var(--color-surface-muted);
    color: var(--color-text-muted);
    cursor: not-allowed;
}
</style>
