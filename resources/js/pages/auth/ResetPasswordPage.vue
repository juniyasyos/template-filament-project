<template>
  <AuthLayout
    title="Reset your password"
    description="Enter your new password below"
    :logo-src="'/favicon.svg'"
    logo-alt="Company Logo"
    logo-href="/"
  >
    <Head title="Reset Password" />

    <!-- Form -->
    <Form
      v-bind="NewPasswordController.store.form()"
      v-slot="{ errors, processing }"
      class="space-y-6"
    >
      <!-- General Form Errors -->
      <FormErrorList :errors="errors" />

      <!-- Hidden Fields for Token and Email -->
      <input type="hidden" name="token" :value="token" />

      <div class="space-y-4">
        <!-- Email Field (Display Only) -->
        <FieldText
          id="email"
          label="Email address"
          type="email"
          name="email"
          :required="true"
          autocomplete="email"
          :model-value="email"
          readonly
          :tabindex="1"
          :error="errors.email"
        />

        <!-- Password Field -->
        <FieldPassword
          id="password"
          label="New password"
          name="password"
          :required="true"
          autocomplete="new-password"
          placeholder="New password"
          :tabindex="2"
          :error="errors.password"
          hint="Must be at least 8 characters long"
        />

        <!-- Confirm Password Field -->
        <FieldPassword
          id="password_confirmation"
          label="Confirm new password"
          name="password_confirmation"
          :required="true"
          autocomplete="new-password"
          placeholder="Confirm new password"
          :tabindex="3"
          :error="errors.password_confirmation"
        />

        <!-- Submit Button -->
        <AppButton
          type="submit"
          variant="primary"
          :loading="processing"
          :disabled="processing"
          block
          :tabindex="4"
          data-test="reset-password-button"
        >
          Reset Password
        </AppButton>
      </div>

      <!-- Back to Login Link -->
      <div class="text-center text-sm text-[var(--muted-foreground)]">
        Remember your password?
        <TextLink :href="login()" :tabindex="5" class="font-medium">
          Back to login
        </TextLink>
      </div>
    </Form>
  </AuthLayout>
</template>

<script setup lang="ts">
import NewPasswordController from '@/actions/App/Http/Controllers/Auth/NewPasswordController';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppButton from '@/components/atoms/AppButton.vue';
import FieldText from '@/components/molecules/FieldText.vue';
import FieldPassword from '@/components/molecules/FieldPassword.vue';
import FormErrorList from '@/components/utilities/FormErrorList.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    email: string;
    token: string;
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

/* Readonly field styling */
input[readonly] {
  opacity: 0.7;
  cursor: not-allowed;
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
