<template>
  <div class="space-y-4">
    <div v-if="title" class="text-center">
      <h3 class="text-sm font-medium text-[var(--fg)]">{{ title }}</h3>
      <p v-if="description" class="text-xs text-[var(--muted-foreground)] mt-1">
        {{ description }}
      </p>
    </div>

    <div class="flex flex-col gap-3">
      <a
        v-for="provider in providers"
        :key="provider.id"
        :href="provider.href || '#'"
        :class="buttonClasses"
        :disabled="disabled"
        @click="handleClick(provider, $event)"
      >
        <component
          v-if="provider.icon"
          :is="provider.icon"
          class="h-5 w-5"
        />
        <span class="flex-1 text-center">{{ provider.label }}</span>
      </a>
    </div>

    <div v-if="showDivider" class="relative">
      <div class="absolute inset-0 flex items-center">
        <span class="w-full border-t border-[var(--border)]"></span>
      </div>
      <div class="relative flex justify-center text-xs uppercase">
        <span class="bg-[var(--bg)] px-2 text-[var(--muted-foreground)]">
          {{ dividerText }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

interface SocialProvider {
  id: string;
  label: string;
  href?: string;
  icon?: Component;
  disabled?: boolean;
}

interface Props {
  providers: SocialProvider[];
  title?: string;
  description?: string;
  disabled?: boolean;
  showDivider?: boolean;
  dividerText?: string;
}

interface Emits {
  click: [provider: SocialProvider, event: MouseEvent];
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  showDivider: true,
  dividerText: 'Or continue with',
});

const emit = defineEmits<Emits>();

const buttonClasses = computed(() => {
  const baseClasses = [
    'flex',
    'items-center',
    'justify-start',
    'gap-3',
    'w-full',
    'px-4',
    'py-3',
    'text-sm',
    'font-medium',
    'bg-[var(--card)]',
    'text-[var(--card-foreground)]',
    'border',
    'border-[var(--border)]',
    'rounded-[var(--radius)]',
    'transition-colors',
    'hover:bg-[var(--muted)]',
    'focus-visible:outline-none',
    'focus-visible:ring-2',
    'focus-visible:ring-[var(--ring)]',
    'focus-visible:ring-offset-2',
    'disabled:opacity-50',
    'disabled:cursor-not-allowed',
    'disabled:hover:bg-[var(--card)]',
    'no-underline',
  ];

  return baseClasses.join(' ');
});

const handleClick = (provider: SocialProvider, event: MouseEvent) => {
  if (props.disabled || provider.disabled) {
    event.preventDefault();
    return;
  }

  emit('click', provider, event);

  // If no href is provided, prevent default navigation
  if (!provider.href || provider.href === '#') {
    event.preventDefault();
  }
};
</script>

<style scoped>
/* Component spacing */
.space-y-4 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1rem;
}

.gap-3 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 0.75rem;
}

/* Link styling reset */
a {
  text-decoration: none;
  color: inherit;
}

/* Focus enhancement */
a:focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
}

/* Hover states */
a:hover:not([disabled]) {
  background-color: var(--muted);
  border-color: var(--border);
}

/* Disabled state */
a[disabled] {
  pointer-events: none;
  opacity: 0.5;
}

/* Divider styling */
.relative .absolute {
  top: 50%;
  transform: translateY(-50%);
}

/* Social provider icons */
.h-5.w-5 {
  flex-shrink: 0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .px-4 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .py-3 {
    padding-top: 0.625rem;
    padding-bottom: 0.625rem;
  }
}

/* Touch targets */
a {
  min-height: 44px;
  touch-action: manipulation;
}

/* High contrast mode */
@media (prefers-contrast: high) {
  a {
    border-width: 2px;
  }

  a:focus {
    border-width: 3px;
  }
}
</style>
