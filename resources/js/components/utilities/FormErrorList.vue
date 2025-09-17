<template>
  <div v-if="hasErrors" class="form-error-list">
    <AppAlert
      variant="danger"
      :title="title"
      :dismissible="dismissible"
      @dismiss="handleDismiss"
    >
      <div v-if="errorType === 'single'" class="single-error">
        {{ firstError }}
      </div>
      
      <ul v-else-if="errorType === 'list'" class="error-list">
        <li v-for="(error, index) in errorArray" :key="index" class="error-item">
          {{ error }}
        </li>
      </ul>
      
      <div v-else-if="errorType === 'object'" class="error-object">
        <div v-for="(error, field) in processedErrors" :key="field" class="field-error">
          <strong>{{ formatFieldName(field) }}:</strong> {{ error }}
        </div>
      </div>
    </AppAlert>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppAlert from '../atoms/AppAlert.vue';

type ErrorInput = string | string[] | Record<string, string | string[]> | null | undefined;

interface Props {
  errors?: ErrorInput;
  title?: string;
  dismissible?: boolean;
  showFieldNames?: boolean;
  maxErrors?: number;
}

interface Emits {
  dismiss: [];
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Please correct the following errors:',
  dismissible: false,
  showFieldNames: true,
  maxErrors: 10,
});

const emit = defineEmits<Emits>();

const hasErrors = computed(() => {
  if (!props.errors) return false;
  
  if (typeof props.errors === 'string') {
    return props.errors.trim().length > 0;
  }
  
  if (Array.isArray(props.errors)) {
    return props.errors.length > 0;
  }
  
  if (typeof props.errors === 'object') {
    return Object.keys(props.errors).length > 0;
  }
  
  return false;
});

const errorType = computed(() => {
  if (!props.errors) return 'none';
  
  if (typeof props.errors === 'string') {
    return 'single';
  }
  
  if (Array.isArray(props.errors)) {
    return 'list';
  }
  
  if (typeof props.errors === 'object') {
    return 'object';
  }
  
  return 'none';
});

const firstError = computed(() => {
  if (typeof props.errors === 'string') {
    return props.errors;
  }
  return '';
});

const errorArray = computed(() => {
  if (Array.isArray(props.errors)) {
    return props.errors.slice(0, props.maxErrors);
  }
  return [];
});

const processedErrors = computed(() => {
  if (typeof props.errors === 'object' && !Array.isArray(props.errors) && props.errors) {
    const processed: Record<string, string> = {};
    let count = 0;
    
    for (const [field, error] of Object.entries(props.errors)) {
      if (count >= props.maxErrors) break;
      
      if (Array.isArray(error)) {
        processed[field] = error[0]; // Take first error for each field
      } else if (typeof error === 'string') {
        processed[field] = error;
      }
      count++;
    }
    
    return processed;
  }
  return {};
});

const formatFieldName = (fieldName: string): string => {
  if (!props.showFieldNames) return '';
  
  // Convert snake_case to Title Case
  return fieldName
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const handleDismiss = () => {
  emit('dismiss');
};
</script>

<style scoped>
/* Form error list container */
.form-error-list {
  margin-bottom: 1rem;
}

/* Single error styling */
.single-error {
  font-size: 0.875rem;
  line-height: 1.5;
}

/* Error list styling */
.error-list {
  margin: 0;
  padding-left: 1.25rem;
  list-style-type: disc;
}

.error-item {
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 0.25rem;
}

.error-item:last-child {
  margin-bottom: 0;
}

/* Error object styling */
.error-object {
  space-y: 0.5rem;
}

.field-error {
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 0.5rem;
}

.field-error:last-child {
  margin-bottom: 0;
}

.field-error strong {
  font-weight: 600;
  color: inherit;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .error-list {
    padding-left: 1rem;
  }
  
  .error-item,
  .single-error,
  .field-error {
    font-size: 0.8125rem;
  }
}

/* High contrast support */
@media (prefers-contrast: high) {
  .field-error strong {
    font-weight: 700;
  }
  
  .error-list {
    list-style-type: '• ';
  }
}

/* Print styles */
@media print {
  .form-error-list {
    page-break-inside: avoid;
    margin-bottom: 0.5rem;
  }
}
</style>