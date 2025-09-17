<template>
  <AuthLayout
    :title="authConfigContent.title"
    :description="authConfigContent.description"
    :logo-src="'/favicon.svg'"
    logo-alt="Company Logo"
    logo-href="/"
  >
    <Head title="Two-Factor Authentication" />

    <div class="space-y-6">
      <!-- Form -->
      <Form
        v-bind="TwoFactorAuthenticatedSessionController.store.form()"
        v-slot="{ errors, processing, clearErrors }"
        class="space-y-6"
      >
        <!-- General Form Errors -->
        <FormErrorList :errors="errors" />

        <div class="space-y-4">
          <!-- OTP Input for Authentication Code -->
          <template v-if="!showRecoveryInput">
            <OtpInput
              id="code"
              v-model="codeValue"
              label="Authentication Code"
              description="Enter the 6-digit code from your authenticator app"
              :length="6"
              :error="errors.code"
              name="code"
              @complete="handleOtpComplete"
            />
          </template>

          <!-- Recovery Code Input -->
          <template v-else>
            <FieldText
              id="recovery_code"
              label="Recovery Code"
              type="text"
              name="recovery_code"
              :required="true"
              autocomplete="one-time-code"
              placeholder="Enter recovery code"
              :tabindex="1"
              :error="errors.recovery_code"
              hint="Enter one of your emergency recovery codes"
            />
          </template>

          <!-- Submit Button -->
          <AppButton
            type="submit"
            variant="primary"
            :loading="processing"
            :disabled="processing"
            block
            data-test="verify-button"
          >
            Verify
          </AppButton>

          <!-- Toggle Mode Button -->
          <AppButton
            type="button"
            variant="ghost"
            block
            @click="toggleRecoveryMode(clearErrors)"
            data-test="toggle-mode-button"
          >
            {{ authConfigContent.toggleText }}
          </AppButton>
        </div>
      </Form>

      <!-- Logout Option -->
      <div class="pt-4 border-t border-[var(--border)]">
        <Form
          v-bind="AuthenticatedSessionController.destroy.form()"
          v-slot="{ processing }"
          class="text-center"
        >
          <button
            type="submit"
            :disabled="processing"
            class="text-sm text-[var(--muted-foreground)] hover:text-[var(--fg)] underline transition-colors disabled:opacity-50"
            data-test="logout-button"
          >
            <span v-if="processing">Logging out...</span>
            <span v-else>Cancel and logout</span>
          </button>
        </Form>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import TwoFactorAuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/TwoFactorAuthenticatedSessionController';
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppButton from '@/components/atoms/AppButton.vue';
import FieldText from '@/components/molecules/FieldText.vue';
import OtpInput from '@/components/molecules/OtpInput.vue';
import FormErrorList from '@/components/utilities/FormErrorList.vue';
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description: 'Please confirm access to your account by entering one of your emergency recovery codes.',
            toggleText: 'Use authentication code instead',
        };
    }

    return {
        title: 'Authentication Code',
        description: 'Enter the authentication code provided by your authenticator application.',
        toggleText: 'Use recovery code instead',
    };
});

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>('');

const codeValue = computed({
    get: () => code.value,
    set: (value: string) => {
        code.value = value;
    },
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};

const handleOtpComplete = (value: string) => {
    code.value = value;
    // Auto-submit when OTP is complete
    // The form will be submitted automatically due to the form binding
};
</script>

<style scoped>
/* Component spacing */
.space-y-6 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1.5rem;
}

.space-y-4 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1rem;
}

/* Form styling using design tokens */
.form-container {
  background: var(--card);
  color: var(--card-foreground);
}

/* Link styling */
.text-muted-foreground {
  color: var(--muted-foreground);
}

/* Border styling */
.border-border {
  border-color: var(--border);
}

/* Button styling for logout */
button[type="submit"] {
  background: none;
  border: none;
  cursor: pointer;
  font-family: inherit;
  transition: color 0.2s ease;
}

button[type="submit"]:hover:not(:disabled) {
  color: var(--fg);
}

button[type="submit"]:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .space-y-6 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 1rem;
  }
  
  .space-y-4 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 0.75rem;
  }
}

/* Focus management */
.auth-form:focus-within {
  outline: none;
}

button:focus-visible {
  outline: 2px solid var(--ring);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
}

/* High contrast support */
@media (prefers-contrast: high) {
  .font-medium {
    font-weight: 700;
  }
  
  .underline {
    text-decoration-thickness: 2px;
  }
}

/* Print styles */
@media print {
  .border-t {
    border-top: 1px solid #ccc;
  }
}
</style>