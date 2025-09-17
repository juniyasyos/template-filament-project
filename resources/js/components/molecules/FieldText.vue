<template>
  <div class="space-y-2">
    <AppLabel
      :html-for="id"
      :required="required"
      :size="labelSize"
    >
      {{ label }}
    </AppLabel>

    <AppInput
      :id="id"
      v-model="localValue"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :required="required"
      :invalid="hasError"
      :autocomplete="autocomplete"
      :aria-describedby="ariaDescribedBy"
      @focus="handleFocus"
      @blur="handleBlur"
      @input="handleInput"
      @keydown="handleKeydown"
    />

    <div v-if="hint && !hasError" class="text-xs text-[var(--muted-foreground)]">
      {{ hint }}
    </div>

    <div v-if="hasError" class="text-xs text-[var(--danger)]" role="alert">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import AppLabel from '../atoms/AppLabel.vue';
import AppInput from '../atoms/AppInput.vue';

interface Props {
  id?: string;
  label: string;
  modelValue?: string | number;
  type?: 'text' | 'email' | 'tel' | 'url' | 'search' | 'number';
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  required?: boolean;
  error?: string;
  hint?: string;
  autocomplete?: string;
  labelSize?: 'sm' | 'md' | 'lg';
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
  labelSize: 'md',
});

const emit = defineEmits<Emits>();

// Generate a unique ID if not provided
const generatedId = useId();
const id = computed(() => props.id || generatedId);

const localValue = computed({
  get: () => props.modelValue ?? '',
  set: (value) => emit('update:modelValue', value),
});

const hasError = computed(() => Boolean(props.error));

const ariaDescribedBy = computed(() => {
  const ids = [];
  if (props.hint && !hasError.value) {
    ids.push(`${id.value}-hint`);
  }
  if (hasError.value) {
    ids.push(`${id.value}-error`);
  }
  return ids.length > 0 ? ids.join(' ') : undefined;
});

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};

const handleInput = (event: Event) => {
  emit('input', event);
};

const handleKeydown = (event: KeyboardEvent) => {
  emit('keydown', event);
};
</script>

<style scoped>
/* Component spacing */
.space-y-2 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 0.5rem;
}

/* Error state styling */
.text-danger {
  color: var(--danger);
}

/* Hint text styling */
.text-muted-foreground {
  color: var(--muted-foreground);
}

/* Responsive text sizes */
@media (max-width: 640px) {
  .text-xs {
    font-size: 0.75rem;
  }
}
</style>
