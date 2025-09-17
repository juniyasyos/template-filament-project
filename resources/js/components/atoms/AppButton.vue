<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <AppSpinner v-if="loading" class="mr-2 h-4 w-4" />
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppSpinner from './AppSpinner.vue';

interface Props {
  type?: 'button' | 'submit' | 'reset';
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
  loading?: boolean;
  block?: boolean;
  disabled?: boolean;
  size?: 'sm' | 'md' | 'lg';
}

interface Emits {
  click: [event: MouseEvent];
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  variant: 'primary',
  loading: false,
  block: false,
  disabled: false,
  size: 'md',
});

const emit = defineEmits<Emits>();

const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex',
    'items-center',
    'justify-center',
    'font-medium',
    'transition-colors',
    'focus-visible:outline-none',
    'focus-visible:ring-2',
    'focus-visible:ring-[var(--focus-ring)]',
    'focus-visible:ring-offset-2',
    'disabled:opacity-50',
    'disabled:pointer-events-none',
    'touch-manipulation', // Better mobile interaction
  ];

  // Size classes
  const sizeClasses = {
    sm: ['text-sm', 'h-8', 'px-3', 'rounded-[var(--radius-sm)]'],
    md: ['text-sm', 'h-10', 'px-4', 'rounded-[var(--radius)]'],
    lg: ['text-base', 'h-12', 'px-6', 'rounded-[var(--radius-lg)]'],
  };

  // Variant classes
  const variantClasses = {
    primary: [
      'bg-[var(--brand)]',
      'text-[var(--brand-foreground)]',
      'hover:bg-[var(--brand-hover)]',
      'active:bg-[var(--brand-active)]',
    ],
    secondary: [
      'bg-[var(--muted)]',
      'text-[var(--muted-foreground)]',
      'hover:bg-[var(--muted)]/80',
      'border',
      'border-[var(--border)]',
    ],
    ghost: [
      'text-[var(--fg)]',
      'hover:bg-[var(--muted)]',
      'hover:text-[var(--muted-foreground)]',
    ],
    danger: [
      'bg-[var(--danger)]',
      'text-[var(--danger-foreground)]',
      'hover:bg-[var(--danger)]/90',
      'active:bg-[var(--danger)]/80',
    ],
  };

  const blockClasses = props.block ? ['w-full'] : [];

  return [
    ...baseClasses,
    ...sizeClasses[props.size],
    ...variantClasses[props.variant],
    ...blockClasses,
  ].join(' ');
});

const handleClick = (event: MouseEvent) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};
</script>

<style scoped>
/* Ensure touch targets are at least 44px for better accessibility */
button {
  min-height: 44px;
  min-width: 44px;
}

/* Custom focus ring using design tokens */
button:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

/* Loading state cursor */
button:disabled {
  cursor: not-allowed;
}

button[aria-busy="true"] {
  cursor: wait;
}
</style>
