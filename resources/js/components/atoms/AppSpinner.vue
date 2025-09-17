<template>
  <div class="spinner" :class="spinnerClasses" role="status" :aria-label="ariaLabel">
    <svg
      class="animate-spin"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <circle
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-dasharray="31.416"
        stroke-dashoffset="31.416"
        class="opacity-25"
      />
      <path
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        fill="currentColor"
      />
    </svg>
    <span class="sr-only">{{ srText }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  size?: 'sm' | 'md' | 'lg';
  color?: 'current' | 'brand' | 'muted';
  ariaLabel?: string;
  srText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  color: 'current',
  ariaLabel: 'Loading',
  srText: 'Loading, please wait...',
});

const spinnerClasses = computed(() => {
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
  };

  const colorClasses = {
    current: 'text-current',
    brand: 'text-[var(--brand)]',
    muted: 'text-[var(--muted-foreground)]',
  };

  return [
    sizeClasses[props.size],
    colorClasses[props.color],
  ].join(' ');
});
</script>

<style scoped>
/* Respect reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
  .animate-spin {
    animation: none;
  }

  /* Provide alternative loading indication */
  .spinner::after {
    content: '⋯';
    animation: pulse 1.5s ease-in-out infinite;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Screen reader only class */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
