<template>
  <div class="flex items-center space-x-2">
    <input
      :id="id"
      v-model="localChecked"
      type="checkbox"
      :disabled="disabled"
      :required="required"
      :aria-describedby="ariaDescribedBy"
      :class="checkboxClasses"
      @change="handleChange"
      @focus="handleFocus"
      @blur="handleBlur"
    />
    <label
      v-if="label"
      :for="id"
      class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
    >
      {{ label }}
      <span v-if="required" class="text-[var(--danger)] ml-1" aria-hidden="true">*</span>
    </label>
    <slot v-else />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  id?: string;
  modelValue?: boolean;
  label?: string;
  disabled?: boolean;
  required?: boolean;
  ariaDescribedBy?: string;
}

interface Emits {
  'update:modelValue': [value: boolean];
  change: [event: Event];
  focus: [event: FocusEvent];
  blur: [event: FocusEvent];
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  disabled: false,
  required: false,
});

const emit = defineEmits<Emits>();

const localChecked = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const checkboxClasses = computed(() => {
  const baseClasses = [
    'peer',
    'h-4',
    'w-4',
    'shrink-0',
    'rounded-sm',
    'border',
    'border-[var(--border)]',
    'bg-[var(--input)]',
    'ring-offset-background',
    'focus-visible:outline-none',
    'focus-visible:ring-2',
    'focus-visible:ring-[var(--ring)]',
    'focus-visible:ring-offset-2',
    'disabled:cursor-not-allowed',
    'disabled:opacity-50',
    'transition-colors',
    'cursor-pointer',
    // Custom checkbox styling
    'appearance-none',
    'relative',
  ];

  return baseClasses.join(' ');
});

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  localChecked.value = target.checked;
  emit('change', event);
};

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};
</script>

<style scoped>
/* Custom checkbox styling */
input[type="checkbox"] {
  min-width: 16px;
  min-height: 16px;
  background: var(--input);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  position: relative;
  transition: all 0.2s ease;
}

/* Checked state */
input[type="checkbox"]:checked {
  background: var(--brand);
  border-color: var(--brand);
}

/* Checkmark */
input[type="checkbox"]:checked::after {
  content: '';
  position: absolute;
  top: 1px;
  left: 4px;
  width: 6px;
  height: 10px;
  border: solid var(--brand-foreground);
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Focus state */
input[type="checkbox"]:focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
}

/* Disabled state */
input[type="checkbox"]:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Ensure touch target is adequate */
input[type="checkbox"] {
  min-width: 44px;
  min-height: 44px;
  padding: 14px; /* Creates the 44px touch target while checkbox itself is 16px */
}

/* Indeterminate state */
input[type="checkbox"]:indeterminate {
  background: var(--brand);
  border-color: var(--brand);
}

input[type="checkbox"]:indeterminate::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 8px;
  height: 2px;
  background: var(--brand-foreground);
  transform: translate(-50%, -50%);
  border: none;
}

/* Label spacing and interaction */
label {
  cursor: pointer;
  user-select: none;
}

/* Container spacing */
.flex.items-center.space-x-2 {
  align-items: flex-start;
  gap: 0.5rem;
}
</style>
