<template>
  <div class="space-y-2">
    <AppLabel
      :html-for="id"
      :required="required"
      :size="labelSize"
    >
      {{ label }}
    </AppLabel>
    
    <div class="relative">
      <AppInput
        :id="id"
        v-model="localValue"
        :type="inputType"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :invalid="hasError"
        :autocomplete="autocomplete"
        :aria-describedby="ariaDescribedBy"
        class="pr-10"
        @focus="handleFocus"
        @blur="handleBlur"
        @input="handleInput"
        @keydown="handleKeydown"
      />
      
      <button
        type="button"
        class="absolute right-0 top-0 h-full px-3 text-[var(--muted-foreground)] hover:text-[var(--fg)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)] focus-visible:ring-offset-2 rounded-r-[var(--radius)] transition-colors"
        :aria-label="showPassword ? 'Hide password' : 'Show password'"
        @click="togglePasswordVisibility"
      >
        <EyeIcon v-if="!showPassword" class="h-4 w-4" />
        <EyeOffIcon v-else class="h-4 w-4" />
      </button>
    </div>
    
    <div v-if="hint && !hasError" class="text-xs text-[var(--muted-foreground)]">
      {{ hint }}
    </div>
    
    <div v-if="hasError" class="text-xs text-[var(--danger)]" role="alert">
      {{ error }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, useId } from 'vue';
import { Eye as EyeIcon, EyeOff as EyeOffIcon } from 'lucide-vue-next';
import AppLabel from '../atoms/AppLabel.vue';
import AppInput from '../atoms/AppInput.vue';

interface Props {
  id?: string;
  label: string;
  modelValue?: string;
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  required?: boolean;
  error?: string;
  hint?: string;
  autocomplete?: string;
  labelSize?: 'sm' | 'md' | 'lg';
}

interface Emits {
  'update:modelValue': [value: string];
  focus: [event: FocusEvent];
  blur: [event: FocusEvent];
  input: [event: Event];
  keydown: [event: KeyboardEvent];
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  readonly: false,
  required: false,
  labelSize: 'md',
  autocomplete: 'current-password',
});

const emit = defineEmits<Emits>();

// Generate a unique ID if not provided
const generatedId = useId();
const id = computed(() => props.id || generatedId);

const showPassword = ref(false);

const inputType = computed(() => showPassword.value ? 'text' : 'password');

const localValue = computed({
  get: () => props.modelValue ?? '',
  set: (value) => emit('update:modelValue', value),
});

const hasError = computed(() => Boolean(props.error));

const ariaDescribedBy = computed(() => {
  const ids = [];
  if (props.hint && !hasError.value) {
    ids.push(`${id.value}-hint`);
  }
  if (hasError.value) {
    ids.push(`${id.value}-error`);
  }
  return ids.length > 0 ? ids.join(' ') : undefined;
});

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const handleFocus = (event: FocusEvent) => {
  emit('focus', event);
};

const handleBlur = (event: FocusEvent) => {
  emit('blur', event);
};

const handleInput = (event: Event) => {
  emit('input', event);
};

const handleKeydown = (event: KeyboardEvent) => {
  emit('keydown', event);
};
</script>

<style scoped>
/* Component spacing */
.space-y-2 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 0.5rem;
}

/* Toggle button styling */
.absolute.right-0 {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 44px; /* Ensure adequate touch target */
  min-height: 44px;
}

/* Focus ring for toggle button */
button:focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
}

/* Error state styling */
.text-danger {
  color: var(--danger);
}

/* Hint text styling */
.text-muted-foreground {
  color: var(--muted-foreground);
}

/* Password visibility icon */
.lucide {
  stroke-width: 1.5;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-xs {
    font-size: 0.75rem;
  }
  
  .absolute.right-0 {
    min-width: 40px;
    min-height: 40px;
  }
}
</style>