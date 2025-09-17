<template>
  <AuthLayout
    title="Forgot your password?"
    description="Enter your email address and we'll send you a link to reset your password"
    :logo-src="'/favicon.svg'"
    logo-alt="Company Logo"
    logo-href="/"
  >
    <Head title="Forgot Password" />

    <!-- Status Message -->
    <AppAlert v-if="status" variant="success" class="mb-6">
      {{ status }}
    </AppAlert>

    <!-- Form -->
    <Form
      v-bind="PasswordResetLinkController.store.form()"
      v-slot="{ errors, processing, wasSuccessful }"
      class="space-y-6"
    >
      <!-- General Form Errors -->
      <FormErrorList :errors="errors" />

      <div class="space-y-4">
        <!-- Email Field -->
        <FieldText
          id="email"
          label="Email address"
          type="email"
          name="email"
          :required="true"
          autocomplete="email"
          placeholder="email@example.com"
          :tabindex="1"
          :error="errors.email"
          hint="We'll send a password reset link to this email address"
        />

        <!-- Submit Button -->
        <AppButton
          type="submit"
          variant="primary"
          :loading="processing"
          :disabled="processing || wasSuccessful"
          block
          :tabindex="2"
          data-test="send-reset-link-button"
        >
          <span v-if="wasSuccessful">Email Sent</span>
          <span v-else>Send Password Reset Link</span>
        </AppButton>
      </div>

      <!-- Back to Login Link -->
      <div class="text-center text-sm text-[var(--muted-foreground)]">
        Remember your password?
        <TextLink :href="login()" :tabindex="3" class="font-medium">
          Back to login
        </TextLink>
      </div>
    </Form>
  </AuthLayout>
</template>

<script setup lang="ts">
import PasswordResetLinkController from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppAlert from '@/components/atoms/AppAlert.vue';
import AppButton from '@/components/atoms/AppButton.vue';
import FieldText from '@/components/molecules/FieldText.vue';
import FormErrorList from '@/components/utilities/FormErrorList.vue';
import { login } from '@/routes';
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

/* Form styling using design tokens */
.form-container {
  background: var(--card);
  color: var(--card-foreground);
}

/* Link styling */
.text-muted-foreground {
  color: var(--muted-foreground);
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
}

/* Focus management */
.auth-form:focus-within {
  outline: none;
}

/* High contrast support */
@media (prefers-contrast: high) {
  .font-medium {
    font-weight: 700;
  }
}
</style>