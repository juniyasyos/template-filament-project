<template>
  <div class="space-y-4">
    <div class="text-center">
      <AppLabel :html-for="`${id}-0`" class="text-sm font-medium">
        {{ label }}
      </AppLabel>
      <p v-if="description" class="text-xs text-[var(--muted-foreground)] mt-1">
        {{ description }}
      </p>
    </div>

    <div class="flex justify-center gap-2">
      <input
        v-for="(digit, index) in digits"
        :key="index"
        :id="`${id}-${index}`"
        ref="inputs"
        v-model="digits[index]"
        type="text"
        inputmode="numeric"
        pattern="[0-9]"
        maxlength="1"
        :class="inputClasses"
        :aria-label="`Digit ${index + 1} of ${length}`"
        :aria-describedby="hasError ? `${id}-error` : undefined"
        @input="handleInput(index, $event)"
        @keydown="handleKeydown(index, $event)"
        @paste="handlePaste"
        @focus="handleFocus(index)"
        @blur="handleBlur(index)"
      />
    </div>

    <div v-if="hint && !hasError" class="text-xs text-center text-[var(--muted-foreground)]">
      {{ hint }}
    </div>

    <div v-if="hasError" :id="`${id}-error`" class="text-xs text-center text-[var(--danger)]" role="alert">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, useId, watch } from 'vue';
import AppLabel from '../atoms/AppLabel.vue';

interface Props {
  id?: string;
  label?: string;
  description?: string;
  modelValue?: string;
  length?: number;
  disabled?: boolean;
  error?: string;
  hint?: string;
  autoSubmit?: boolean;
}

interface Emits {
  'update:modelValue': [value: string];
  complete: [value: string];
  focus: [index: number];
  blur: [index: number];
}

const props = withDefaults(defineProps<Props>(), {
  length: 6,
  disabled: false,
  autoSubmit: true,
  label: 'Enter verification code',
});

const emit = defineEmits<Emits>();

// Generate a unique ID if not provided
const generatedId = useId();
const id = computed(() => props.id || generatedId);

const inputs = ref<HTMLInputElement[]>([]);
const digits = ref<string[]>(Array(props.length).fill(''));

const hasError = computed(() => Boolean(props.error));

const inputClasses = computed(() => {
  const baseClasses = [
    'w-12',
    'h-12',
    'text-center',
    'text-lg',
    'font-semibold',
    'border',
    'border-[var(--border)]',
    'rounded-[var(--radius)]',
    'bg-[var(--input)]',
    'text-[var(--input-foreground)]',
    'focus-visible:outline-none',
    'focus-visible:ring-2',
    'focus-visible:ring-[var(--ring)]',
    'focus-visible:ring-offset-2',
    'disabled:opacity-50',
    'disabled:cursor-not-allowed',
    'transition-colors',
  ];

  const errorClasses = hasError.value
    ? [
        'border-[var(--danger)]',
        'focus-visible:ring-[var(--danger)]',
        'bg-[var(--danger)]/5',
      ]
    : [];

  return [...baseClasses, ...errorClasses].join(' ');
});

// Watch for external value changes
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== undefined) {
      const valueArray = newValue.split('').slice(0, props.length);
      digits.value = [...valueArray, ...Array(props.length - valueArray.length).fill('')];
    }
  },
  { immediate: true }
);

// Update model value when digits change
watch(
  digits,
  (newDigits) => {
    const value = newDigits.join('');
    emit('update:modelValue', value);

    // Auto-submit when all digits are filled
    if (props.autoSubmit && value.length === props.length && !value.includes('')) {
      emit('complete', value);
    }
  },
  { deep: true }
);

const handleInput = (index: number, event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value.replace(/\D/g, ''); // Only allow digits

  if (value.length > 0) {
    digits.value[index] = value[0];

    // Move to next input
    if (index < props.length - 1) {
      focusInput(index + 1);
    }
  }
};

const handleKeydown = (index: number, event: KeyboardEvent) => {
  if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
    // Move to previous input on backspace if current is empty
    focusInput(index - 1);
  } else if (event.key === 'ArrowLeft' && index > 0) {
    focusInput(index - 1);
  } else if (event.key === 'ArrowRight' && index < props.length - 1) {
    focusInput(index + 1);
  } else if (event.key === 'Delete') {
    digits.value[index] = '';
  } else if (event.key === 'Enter') {
    // Submit on Enter if all digits are filled
    const value = digits.value.join('');
    if (value.length === props.length) {
      emit('complete', value);
    }
  }
};

const handlePaste = (event: ClipboardEvent) => {
  event.preventDefault();
  const pastedData = event.clipboardData?.getData('text') || '';
  const digitsOnly = pastedData.replace(/\D/g, '').slice(0, props.length);

  if (digitsOnly.length > 0) {
    const newDigits = [...digits.value];
    digitsOnly.split('').forEach((digit, index) => {
      if (index < props.length) {
        newDigits[index] = digit;
      }
    });
    digits.value = newDigits;

    // Focus the next empty input or the last input
    const nextEmptyIndex = newDigits.findIndex(digit => !digit);
    const focusIndex = nextEmptyIndex === -1 ? props.length - 1 : nextEmptyIndex;
    nextTick(() => focusInput(focusIndex));
  }
};

const focusInput = (index: number) => {
  if (inputs.value[index]) {
    inputs.value[index].focus();
    inputs.value[index].select();
  }
};

const handleFocus = (index: number) => {
  emit('focus', index);
};

const handleBlur = (index: number) => {
  emit('blur', index);
};

// Clear all digits
const clear = () => {
  digits.value = Array(props.length).fill('');
  focusInput(0);
};

// Focus first input
const focus = () => {
  focusInput(0);
};

// Expose methods for parent components
defineExpose({
  clear,
  focus,
});
</script>

<style scoped>
/* Component spacing */
.space-y-4 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1rem;
}

/* Input styling */
input {
  caret-color: var(--brand);
}

/* Focus state enhancement */
input:focus {
  transform: scale(1.05);
  transition: transform 0.1s ease;
}

/* Error state styling */
.text-danger {
  color: var(--danger);
}

/* Hint text styling */
.text-muted-foreground {
  color: var(--muted-foreground);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .w-12 {
    width: 2.5rem;
  }

  .h-12 {
    height: 2.5rem;
  }

  .text-lg {
    font-size: 1rem;
  }

  .gap-2 {
    gap: 0.375rem;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  input {
    border-width: 2px;
  }

  input:focus {
    border-width: 3px;
  }
}
</style>
