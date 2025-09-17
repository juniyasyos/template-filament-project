<template>
  <div class="auth-header space-y-6">
    <!-- Logo Section -->
    <div v-if="showLogo" class="flex justify-center">
      <a
        v-if="logoHref"
        :href="logoHref"
        class="inline-flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)] focus-visible:ring-offset-2 rounded-[var(--radius)]"
        :aria-label="logoAlt || 'Go to homepage'"
      >
        <img
          v-if="logoSrc"
          :src="logoSrc"
          :alt="logoAlt"
          :class="logoClasses"
        />
        <slot v-else name="logo" />
      </a>
      <div v-else class="inline-flex items-center justify-center">
        <img
          v-if="logoSrc"
          :src="logoSrc"
          :alt="logoAlt"
          :class="logoClasses"
        />
        <slot v-else name="logo" />
      </div>
    </div>

    <!-- Text Content -->
    <div class="text-center space-y-2">
      <h1 v-if="title" :class="titleClasses">
        {{ title }}
      </h1>

      <p v-if="subtitle" :class="subtitleClasses">
        {{ subtitle }}
      </p>

      <!-- Additional content slot -->
      <div v-if="$slots.default" class="mt-4">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  title?: string;
  subtitle?: string;
  logoSrc?: string;
  logoAlt?: string;
  logoHref?: string;
  showLogo?: boolean;
  titleSize?: 'sm' | 'md' | 'lg' | 'xl';
  alignment?: 'left' | 'center' | 'right';
}

const props = withDefaults(defineProps<Props>(), {
  showLogo: true,
  titleSize: 'lg',
  alignment: 'center',
});

const logoClasses = computed(() => {
  const baseClasses = [
    'transition-transform',
    'duration-200',
    'hover:scale-105',
  ];

  // Default logo size
  const sizeClasses = ['h-12', 'w-auto'];

  return [...baseClasses, ...sizeClasses].join(' ');
});

const titleClasses = computed(() => {
  const baseClasses = [
    'font-semibold',
    'text-[var(--fg)]',
    'tracking-tight',
  ];

  const alignmentClasses = {
    left: ['text-left'],
    center: ['text-center'],
    right: ['text-right'],
  };

  const sizeClasses = {
    sm: ['text-lg'],
    md: ['text-xl'],
    lg: ['text-2xl'],
    xl: ['text-3xl'],
  };

  return [
    ...baseClasses,
    ...sizeClasses[props.titleSize],
    ...alignmentClasses[props.alignment],
  ].join(' ');
});

const subtitleClasses = computed(() => {
  const baseClasses = [
    'text-[var(--muted-foreground)]',
    'leading-relaxed',
  ];

  const alignmentClasses = {
    left: ['text-left'],
    center: ['text-center'],
    right: ['text-right'],
  };

  const sizeClasses = ['text-sm'];

  return [
    ...baseClasses,
    ...sizeClasses,
    ...alignmentClasses[props.alignment],
  ].join(' ');
});
</script>

<style scoped>
/* Component spacing */
.space-y-6 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1.5rem;
}

.space-y-2 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 0.5rem;
}

/* Logo interaction */
a:hover img {
  transform: scale(1.05);
}

/* Focus styles */
a:focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
}

/* Typography using design tokens */
.text-fg {
  color: var(--fg);
}

.text-muted-foreground {
  color: var(--muted-foreground);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-3xl {
    font-size: 1.875rem;
    line-height: 2.25rem;
  }

  .text-2xl {
    font-size: 1.5rem;
    line-height: 2rem;
  }

  .space-y-6 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 1rem;
  }
}

/* High contrast support */
@media (prefers-contrast: high) {
  h1 {
    font-weight: 700;
  }

  p {
    font-weight: 500;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .transition-transform {
    transition: none;
  }

  .hover\:scale-105:hover {
    transform: none;
  }
}

/* Print styles */
@media print {
  .auth-header {
    page-break-inside: avoid;
  }

  img {
    max-height: 2rem;
  }
}
</style>
