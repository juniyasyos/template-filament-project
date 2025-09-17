<template>
  <label
    :for="htmlFor"
    :class="labelClasses"
  >
    <slot />
    <span v-if="required" class="text-[var(--danger)] ml-1" aria-hidden="true">*</span>
  </label>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  htmlFor?: string;
  required?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'muted';
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  size: 'md',
  variant: 'default',
});

const labelClasses = computed(() => {
  const baseClasses = [
    'font-medium',
    'leading-none',
    'peer-disabled:cursor-not-allowed',
    'peer-disabled:opacity-70',
  ];

  const sizeClasses = {
    sm: ['text-xs'],
    md: ['text-sm'],
    lg: ['text-base'],
  };

  const variantClasses = {
    default: ['text-[var(--fg)]'],
    muted: ['text-[var(--muted-foreground)]'],
  };

  return [
    ...baseClasses,
    ...sizeClasses[props.size],
    ...variantClasses[props.variant],
  ].join(' ');
});
</script>

<style scoped>
/* Ensure labels are clickable and have adequate spacing */
label {
  cursor: pointer;
  display: block;
  margin-bottom: 0.5rem;
}

/* Required indicator styling */
label .required-indicator {
  color: var(--danger);
  margin-left: 0.25rem;
  font-weight: bold;
}

/* Disabled state */
label:has(+ input:disabled),
label:has(+ textarea:disabled),
label:has(+ select:disabled) {
  cursor: not-allowed;
  opacity: 0.7;
}
</style>
