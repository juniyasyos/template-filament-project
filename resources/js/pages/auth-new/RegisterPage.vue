<template>
  <AuthLayout
    title="Create an account"
    description="Enter your details below to create your account"
    :logo-src="'/favicon.svg'"
    logo-alt="Company Logo"
    logo-href="/"
  >
    <Head title="Register" />

    <!-- Form -->
    <Form
      v-bind="RegisteredUserController.store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="space-y-6"
    >
      <!-- General Form Errors -->
      <FormErrorList :errors="errors" />

      <div class="space-y-4">
        <!-- Name Field -->
        <FieldText
          id="name"
          label="Name"
          type="text"
          name="name"
          :required="true"
          autocomplete="name"
          placeholder="Full name"
          :tabindex="1"
          :error="errors.name"
        />

        <!-- Email Field -->
        <FieldText
          id="email"
          label="Email address"
          type="email"
          name="email"
          :required="true"
          autocomplete="email"
          placeholder="email@example.com"
          :tabindex="2"
          :error="errors.email"
        />

        <!-- Password Field -->
        <FieldPassword
          id="password"
          label="Password"
          name="password"
          :required="true"
          autocomplete="new-password"
          placeholder="Password"
          :tabindex="3"
          :error="errors.password"
          hint="Must be at least 8 characters long"
        />

        <!-- Confirm Password Field -->
        <FieldPassword
          id="password_confirmation"
          label="Confirm password"
          name="password_confirmation"
          :required="true"
          autocomplete="new-password"
          placeholder="Confirm password"
          :tabindex="4"
          :error="errors.password_confirmation"
        />

        <!-- Submit Button -->
        <AppButton
          type="submit"
          variant="primary"
          :loading="processing"
          :disabled="processing"
          block
          :tabindex="5"
          data-test="register-button"
        >
          Create account
        </AppButton>
      </div>

      <!-- Login Link -->
      <div class="text-center text-sm text-[var(--muted-foreground)]">
        Already have an account?
        <TextLink :href="login()" :tabindex="6" class="font-medium">
          Sign in
        </TextLink>
      </div>
    </Form>
  </AuthLayout>
</template>

<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import TextLink from '@/components/TextLink.vue';
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppButton from '@/components/atoms/AppButton.vue';
import FieldText from '@/components/molecules/FieldText.vue';
import FieldPassword from '@/components/molecules/FieldPassword.vue';
import FormErrorList from '@/components/utilities/FormErrorList.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
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
