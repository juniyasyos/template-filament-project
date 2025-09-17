<template>
  <AuthLayout
    title="Verify your email"
    description="Check your email for a verification link"
    :logo-src="'/favicon.svg'"
    logo-alt="Company Logo"
    logo-href="/"
  >
    <Head title="Verify Email" />

    <div class="space-y-6">
      <!-- Information Message -->
      <AppAlert variant="info">
        <div class="space-y-2">
          <p class="font-medium">Email verification required</p>
          <p class="text-sm">
            Before continuing, please check your email for a verification link.
            If you didn't receive the email, we can send you another one.
          </p>
        </div>
      </AppAlert>

      <!-- Status Message -->
      <AppAlert v-if="status === 'verification-link-sent'" variant="success">
        A new verification link has been sent to your email address.
      </AppAlert>

      <!-- Resend Form -->
      <Form
        v-bind="EmailVerificationNotificationController.store.form()"
        v-slot="{ processing, wasSuccessful }"
        class="space-y-4"
      >
        <AppButton
          type="submit"
          variant="primary"
          :loading="processing"
          :disabled="processing || wasSuccessful"
          block
          data-test="resend-verification-button"
        >
          <span v-if="wasSuccessful">Verification Email Sent</span>
          <span v-else>Resend Verification Email</span>
        </AppButton>
      </Form>

      <!-- Actions -->
      <div class="flex flex-col gap-3 pt-4 border-t border-[var(--border)]">
        <!-- Dashboard Link (if user has access) -->
        <TextLink
          :href="dashboard()"
          class="text-center text-sm font-medium"
          data-test="dashboard-link"
        >
          Continue to Dashboard
        </TextLink>

        <!-- Logout Link -->
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
            <span v-else>Logout</span>
          </button>
        </Form>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppAlert from '@/components/atoms/AppAlert.vue';
import AppButton from '@/components/atoms/AppButton.vue';
import { dashboard } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();
</script>

<style scoped>
/* Component spacing */
.space-y-6 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1.5rem;
}

.space-y-4 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 1rem;
}

.space-y-2 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 0.5rem;
}

.gap-3 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 0.75rem;
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

/* Success state styling */
.success-state {
  color: var(--success);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .space-y-6 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 1rem;
  }

  .space-y-4 > :not([hidden]) ~ :not([hidden]) {
    margin-top: 0.75rem;
  }

  .flex-col {
    align-items: stretch;
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
