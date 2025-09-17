<template>
  <AuthLayout
    title="Log in to your account"
    description="Enter your email and password below to log in"
    :logo-src="'/favicon.svg'"
    logo-alt="Company Logo"
    logo-href="/"
  >
    <Head title="Log in" />

    <!-- Status Message -->
    <AppAlert v-if="status" variant="success" class="mb-6">
      {{ status }}
    </AppAlert>

    <!-- Form -->
    <Form
      v-bind="AuthenticatedSessionController.store.form()"
      :reset-on-success="['password']"
      v-slot="{ errors, processing }"
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
          :default-value="devAutofill?.email"
          :tabindex="1"
          :error="errors.email"
        />

        <!-- Password Field -->
        <div class="space-y-4">
          <FieldPassword
            id="password"
            label="Password"
            name="password"
            :required="true"
            autocomplete="current-password"
            placeholder="Password"
            :default-value="devAutofill?.password"
            :tabindex="2"
            :error="errors.password"
          />

          <!-- Forgot Password Link -->
          <div v-if="canResetPassword" class="text-right">
            <TextLink :href="request()" class="text-sm" :tabindex="5">
              Forgot password?
            </TextLink>
          </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <AppCheckbox
            id="remember"
            name="remember"
            label="Remember me"
            :tabindex="3"
          />
        </div>

        <!-- Submit Button -->
        <AppButton
          type="submit"
          variant="primary"
          :loading="processing"
          :disabled="processing"
          block
          :tabindex="4"
          data-test="login-button"
        >
          Log in
        </AppButton>
      </div>

      <!-- Register Link -->
      <div class="text-center text-sm text-[var(--muted-foreground)]">
        Don't have an account?
        <TextLink :href="register()" :tabindex="6" class="font-medium">
          Sign up
        </TextLink>
      </div>
    </Form>
  </AuthLayout>
</template>

<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppAlert from '@/components/atoms/AppAlert.vue';
import AppButton from '@/components/atoms/AppButton.vue';
import AppCheckbox from '@/components/atoms/AppCheckbox.vue';
import FieldText from '@/components/molecules/FieldText.vue';
import FieldPassword from '@/components/molecules/FieldPassword.vue';
import FormErrorList from '@/components/utilities/FormErrorList.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    devAutofill?: { email?: string; password?: string } | null;
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