<template>
  <input
    :id="id"
    v-model="localValue"
    :type="type"
    :placeholder="placeholder"
    :disabled="disabled"
    :readonly="readonly"
    :required="required"
    :autocomplete="autocomplete"
    :aria-invalid="invalid ? 'true' : undefined"
    :aria-describedby="ariaDescribedBy"
    :class="inputClasses"
    @focus="handleFocus"
    @blur="handleBlur"
    @input="handleInput"
    @keydown="handleKeydown"
  />
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
  id?: string;
  modelValue?: string | number;
  type?: 'text' | 'email' | 'password' | 'tel' | 'url' | 'search' | 'number';
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  required?: boolean;
  invalid?: boolean;
  autocomplete?: string;
  ariaDescribedBy?: string;
}

interface Emits {
  'update:modelValue': [value: string | number];
  focus: [event: FocusEvent];
  blur: [event: FocusEvent];
  input: [event: Event];
  keydown: [event: KeyboardEvent];
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  disabled: false,
  readonly: false,
  required: false,
  invalid: false,
});

const emit = defineEmits<Emits>();

const localValue = computed({
  get: () => props.modelValue ?? '',
  set: (value) => emit('update:modelValue', value),
});

const inputClasses = computed(() => {
  const baseClasses = [
    'flex',
    'h-10',
    'w-full',
    'rounded-[var(--radius)]',
    'border',
    'border-[var(--border)]',
    'bg-[var(--input)]',
    'px-3',
    'py-2',
    'text-sm',
    'text-[var(--input-foreground)]',
    'ring-offset-background',
    'file:border-0',
    'file:bg-transparent',
    'file:text-sm',
    'file:font-medium',
    'placeholder:text-[var(--muted-foreground)]',
    'focus-visible:outline-none',
    'focus-visible:ring-2',
    'focus-visible:ring-[var(--ring)]',
    'focus-visible:ring-offset-2',
    'disabled:cursor-not-allowed',
    'disabled:opacity-50',
    'transition-colors',
  ];

  const stateClasses = props.invalid
    ? [
        'border-[var(--danger)]',
        'focus-visible:ring-[var(--danger)]',
        'bg-[var(--danger)]/5',
      ]
    : [];

  return [...baseClasses, ...stateClasses].join(' ');
});

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  localValue.value = props.type === 'number' ? Number(target.value) : target.value;
  emit('input', event);
};

const handleKeydown = (event: KeyboardEvent) => {
  emit('keydown', event);
};
</script>

<style scoped>
/* Ensure minimum touch target size */
input {
  min-height: 44px;
}

/* Custom focus styles using design tokens */
input:focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
}

/* Invalid state styling */
input[aria-invalid="true"] {
  border-color: var(--danger);
  box-shadow: 0 0 0 1px var(--danger);
}

input[aria-invalid="true"]:focus {
  box-shadow: 0 0 0 2px var(--danger);
}

/* Auto-fill styling for better dark mode support */
input:-webkit-autofill {
  -webkit-box-shadow: 0 0 0 1000px var(--input) inset;
  -webkit-text-fill-color: var(--input-foreground);
}

input:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0 1000px var(--input) inset;
  -webkit-text-fill-color: var(--input-foreground);
}
</style>