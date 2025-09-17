<template>
  <div :class="cardClasses">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  variant?: 'default' | 'elevated' | 'outlined';
  padding?: 'sm' | 'md' | 'lg' | 'xl';
  maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  padding: 'lg',
  maxWidth: 'sm',
});

const cardClasses = computed(() => {
  const baseClasses = [
    'bg-[var(--card)]',
    'text-[var(--card-foreground)]',
    'rounded-[var(--radius-lg)]',
    'transition-all',
    'duration-200',
  ];

  // Variant styles
  const variantClasses = {
    default: [
      'border',
      'border-[var(--border)]',
      'shadow-[var(--shadow-md)]',
    ],
    elevated: [
      'shadow-[var(--shadow-lg)]',
      'border',
      'border-[var(--border)]/50',
    ],
    outlined: [
      'border-2',
      'border-[var(--border)]',
      'shadow-[var(--shadow-sm)]',
    ],
  };

  // Padding styles
  const paddingClasses = {
    sm: ['p-4'],
    md: ['p-6'],
    lg: ['p-8'],
    xl: ['p-10'],
  };

  // Max width styles
  const maxWidthClasses = {
    sm: ['max-w-sm'],
    md: ['max-w-md'],
    lg: ['max-w-lg'],
    xl: ['max-w-xl'],
    full: ['max-w-full'],
  };

  return [
    ...baseClasses,
    ...variantClasses[props.variant],
    ...paddingClasses[props.padding],
    ...maxWidthClasses[props.maxWidth],
    'w-full',
  ].join(' ');
});
</script>

<style scoped>
/* Ensure proper card styling with design tokens */
.bg-card {
  background-color: var(--card);
}

.text-card-foreground {
  color: var(--card-foreground);
}

.border-border {
  border-color: var(--border);
}

/* Enhanced shadow with design tokens */
.shadow-sm {
  box-shadow: var(--shadow-sm);
}

.shadow-md {
  box-shadow: var(--shadow-md);
}

.shadow-lg {
  box-shadow: var(--shadow-lg);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .p-8 {
    padding: 1.5rem;
  }

  .p-10 {
    padding: 2rem;
  }
}

/* Focus management for contained interactive elements */
.auth-card:focus-within {
  outline: none;
}

/* High contrast support */
@media (prefers-contrast: high) {
  .border {
    border-width: 2px;
  }

  .border-2 {
    border-width: 3px;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .transition-all {
    transition: none;
  }
}
</style>
