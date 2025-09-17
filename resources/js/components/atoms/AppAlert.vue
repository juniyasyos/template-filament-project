<template>
  <div
    :class="alertClasses"
    role="alert"
    :aria-live="live"
  >
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0 mt-0.5">
        <component :is="iconComponent" class="h-4 w-4" />
      </div>
      <div class="flex-1">
        <h4 v-if="title" class="font-medium mb-1">
          {{ title }}
        </h4>
        <div class="text-sm">
          <slot>
            {{ message }}
          </slot>
        </div>
      </div>
      <button
        v-if="dismissible"
        type="button"
        class="flex-shrink-0 ml-2 -mt-0.5 -mr-0.5 p-1 rounded-md hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-current transition-colors"
        @click="handleDismiss"
      >
        <span class="sr-only">Dismiss</span>
        <X class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
  CheckCircle,
  AlertCircle,
  AlertTriangle,
  Info,
  X
} from 'lucide-vue-next';

interface Props {
  variant?: 'info' | 'success' | 'warning' | 'danger';
  title?: string;
  message?: string;
  dismissible?: boolean;
  live?: 'polite' | 'assertive' | 'off';
}

interface Emits {
  dismiss: [];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'info',
  dismissible: false,
  live: 'polite',
});

const emit = defineEmits<Emits>();

const iconComponent = computed(() => {
  const icons = {
    info: Info,
    success: CheckCircle,
    warning: AlertTriangle,
    danger: AlertCircle,
  };
  return icons[props.variant];
});

const alertClasses = computed(() => {
  const baseClasses = [
    'rounded-[var(--radius)]',
    'border',
    'p-4',
    'transition-colors',
  ];

  const variantClasses = {
    info: [
      'bg-[var(--info)]/10',
      'text-[var(--info)]',
      'border-[var(--info)]/20',
    ],
    success: [
      'bg-[var(--success)]/10',
      'text-[var(--success)]',
      'border-[var(--success)]/20',
    ],
    warning: [
      'bg-[var(--warning)]/10',
      'text-[var(--warning)]',
      'border-[var(--warning)]/20',
    ],
    danger: [
      'bg-[var(--danger)]/10',
      'text-[var(--danger)]',
      'border-[var(--danger)]/20',
    ],
  };

  return [...baseClasses, ...variantClasses[props.variant]].join(' ');
});

const handleDismiss = () => {
  emit('dismiss');
};
</script>

<style scoped>
/* Ensure sufficient color contrast */
.text-info {
  color: var(--info);
}

.text-success {
  color: var(--success);
}

.text-warning {
  color: var(--warning);
}

.text-danger {
  color: var(--danger);
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

/* Focus styles for dismiss button */
button:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .flex.items-start.gap-3 {
    gap: 0.5rem;
  }

  .p-4 {
    padding: 0.75rem;
  }
}
</style>
