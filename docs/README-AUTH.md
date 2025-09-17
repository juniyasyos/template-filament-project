# Auth UI Documentation

## Overview

This authentication UI system implements **Atomic Design principles** for Vue.js applications using Laravel Fortify backend and Inertia.js forms. The system provides a comprehensive, accessible, and customizable authentication experience without modifying existing routing, controllers, or endpoints.

## ⚠️ Important Constraints

- **No routing changes**: All existing Laravel routes and endpoints remain unchanged
- **No backend modifications**: Controllers, middleware, and auth logic are preserved
- **Inertia.js compatibility**: Uses existing Inertia Form components and patterns
- **UI/UX focus only**: Concentrates on component structure, theming, and user experience

## 🎯 Features

- ✅ **Atomic Design Structure**: Atoms, Molecules, Organisms, Templates
- ✅ **Design Token System**: CSS custom properties for theming
- ✅ **Dark Mode**: Automatic system preference detection
- ✅ **Accessibility**: WCAG AA compliance, keyboard navigation, screen readers
- ✅ **Responsive Design**: Mobile-first approach with touch-friendly interactions
- ✅ **Form Validation**: Centralized error handling and field-level validation
- ✅ **TypeScript Support**: Full type safety throughout the component system

## 📁 Folder Structure

```
resources/js/
├── assets/styles/
│   ├── tokens.css          # Design system variables
│   └── auth.css           # Auth-specific styles
├── components/
│   ├── atoms/             # Basic building blocks
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppLabel.vue
│   │   ├── AppCheckbox.vue
│   │   ├── AppAlert.vue
│   │   └── AppSpinner.vue
│   ├── molecules/         # Component combinations
│   │   ├── FieldText.vue
│   │   ├── FieldPassword.vue
│   │   ├── OtpInput.vue
│   │   └── SocialLoginRow.vue
│   ├── organisms/         # Complex components
│   │   ├── AuthCard.vue
│   │   └── AuthHeader.vue
│   ├── templates/         # Page layouts
│   │   └── AuthLayout.vue
│   └── utilities/         # Helper components
│       └── FormErrorList.vue
└── pages/auth-new/        # Updated auth pages
    ├── LoginPage.vue
    ├── RegisterPage.vue
    ├── ForgotPasswordPage.vue
    ├── ResetPasswordPage.vue
    ├── VerifyEmailPage.vue
    └── TwoFactorChallengePage.vue
```

## 🎨 Design Token System

### Available Tokens

The design system uses CSS custom properties that automatically adapt to dark mode:

```css
/* Brand Colors */
--brand: Primary brand color
--brand-foreground: Text on brand backgrounds
--brand-hover: Brand hover state
--brand-active: Brand active state

/* Surface Colors */
--bg: Page background
--fg: Primary text color
--card: Card/container background
--card-foreground: Text on cards
--muted: Muted background
--muted-foreground: Muted text
--border: Border color
--input: Input background
--input-foreground: Input text

/* State Colors */
--success: Success state color
--warning: Warning state color
--danger: Error/danger state color
--info: Info state color

/* Interactive */
--ring: Focus ring color
--hover-overlay: Hover overlay
--active-overlay: Active overlay

/* Layout */
--radius: Default border radius
--radius-sm: Small border radius
--radius-lg: Large border radius
--auth-card-width: Auth card max width
```

### Customization Example

```css
/* Override in your main CSS file */
:root {
  --brand: hsl(210 100% 50%);           /* Custom blue */
  --brand-foreground: hsl(0 0% 100%);   /* White text */
  --radius: 0.75rem;                    /* Larger radius */
}
```

## 🧱 Component Reference

### Atoms

#### AppButton
```vue
<AppButton
  type="submit"
  variant="primary"     // primary|secondary|ghost|danger
  :loading="isLoading"
  :disabled="false"
  block                 // Full width
  size="md"            // sm|md|lg
>
  Submit
</AppButton>
```

#### AppInput
```vue
<AppInput
  v-model="value"
  type="email"
  placeholder="email@example.com"
  :invalid="hasError"
  autocomplete="email"
  required
/>
```

#### AppCheckbox
```vue
<AppCheckbox
  v-model="checked"
  label="Remember me"
  required
/>
```

#### AppAlert
```vue
<AppAlert
  variant="success"     // info|success|warning|danger
  title="Success"
  message="Operation completed"
  :dismissible="true"
  @dismiss="handleDismiss"
/>
```

### Molecules

#### FieldText
```vue
<FieldText
  v-model="email"
  label="Email Address"
  type="email"
  placeholder="email@example.com"
  :error="errors.email"
  hint="We'll never share your email"
  autocomplete="email"
  required
/>
```

#### FieldPassword
```vue
<FieldPassword
  v-model="password"
  label="Password"
  placeholder="Password"
  :error="errors.password"
  hint="Must be at least 8 characters"
  autocomplete="current-password"
  required
/>
```

#### OtpInput
```vue
<OtpInput
  v-model="otpCode"
  label="Verification Code"
  description="Enter the 6-digit code"
  :length="6"
  :error="errors.code"
  @complete="handleComplete"
/>
```

#### SocialLoginRow
```vue
<SocialLoginRow
  :providers="socialProviders"
  title="Continue with"
  @click="handleSocialLogin"
/>
```

### Organisms

#### AuthCard
```vue
<AuthCard
  variant="elevated"    // default|elevated|outlined
  padding="lg"         // sm|md|lg|xl
  max-width="md"       // sm|md|lg|xl|full
>
  <!-- Card content -->
</AuthCard>
```

#### AuthHeader
```vue
<AuthHeader
  title="Welcome Back"
  subtitle="Sign in to your account"
  logo-src="/logo.svg"
  logo-alt="Company Logo"
  logo-href="/"
  :show-logo="true"
  title-size="lg"      // sm|md|lg|xl
/>
```

### Templates

#### AuthLayout
```vue
<AuthLayout
  title="Login"
  description="Enter your credentials"
  :logo-src="'/logo.svg'"
  :show-header="true"
  :show-footer="true"
  card-variant="default"
  card-padding="lg"
>
  <!-- Form content -->
  
  <template #footer>
    <p>Custom footer content</p>
  </template>
</AuthLayout>
```

### Utilities

#### FormErrorList
```vue
<FormErrorList
  :errors="formErrors"
  title="Please fix the following:"
  :dismissible="false"
  :show-field-names="true"
  :max-errors="5"
/>
```

## 🔧 Usage Patterns

### Basic Form Setup

```vue
<template>
  <AuthLayout title="Login" description="Welcome back">
    <Form v-bind="controller.form()" v-slot="{ errors, processing }">
      <FormErrorList :errors="errors" />
      
      <div class="space-y-4">
        <FieldText
          v-model="form.email"
          label="Email"
          type="email"
          :error="errors.email"
          required
        />
        
        <FieldPassword
          v-model="form.password"
          label="Password"
          :error="errors.password"
          required
        />
        
        <AppButton type="submit" :loading="processing" block>
          Login
        </AppButton>
      </div>
    </Form>
  </AuthLayout>
</template>
```

### Social Login Integration

```vue
<script setup>
const socialProviders = [
  {
    id: 'google',
    label: 'Continue with Google',
    href: '/auth/google',
    icon: GoogleIcon,
  },
  {
    id: 'github',
    label: 'Continue with GitHub',
    href: '/auth/github',
    icon: GitHubIcon,
  },
];
</script>

<template>
  <SocialLoginRow
    :providers="socialProviders"
    @click="handleSocialLogin"
  />
</template>
```

## 🎯 Migration Guide

### Step 1: Import CSS Tokens

Add to your main CSS file:

```css
@import './assets/styles/tokens.css';
@import './assets/styles/auth.css';
```

### Step 2: Replace Existing Pages

Replace the content of existing auth pages with the new components:

```vue
<!-- Old: resources/js/pages/auth/Login.vue -->
<script setup>
import { Button } from '@/components/ui/button';
// ... old imports
</script>

<!-- New: Use the atomic components -->
<script setup>
import AuthLayout from '@/components/templates/AuthLayout.vue';
import AppButton from '@/components/atoms/AppButton.vue';
// ... new imports
</script>
```

### Step 3: Update Components Gradually

You can migrate components one by one:

1. Start with `AuthLayout` wrapper
2. Replace buttons with `AppButton`
3. Replace inputs with `FieldText`/`FieldPassword`
4. Add `FormErrorList` for better error handling

## 🌙 Dark Mode

Dark mode is handled automatically through CSS custom properties and system preferences. No JavaScript required.

### Manual Override (Optional)

```javascript
// Force dark mode
document.documentElement.setAttribute('data-theme', 'dark');

// Force light mode
document.documentElement.setAttribute('data-theme', 'light');

// Reset to system preference
document.documentElement.removeAttribute('data-theme');
```

## ♿ Accessibility Features

### Built-in Accessibility

- **Keyboard Navigation**: All interactive elements are keyboard accessible
- **Screen Reader Support**: Proper ARIA labels and descriptions
- **Focus Management**: Visible focus indicators and logical tab order
- **Color Contrast**: WCAG AA compliant color combinations
- **Touch Targets**: Minimum 44px touch targets for mobile
- **Error Handling**: Errors are announced to screen readers

### Custom Accessibility

```vue
<!-- Custom ARIA labels -->
<AppButton aria-label="Close dialog">×</AppButton>

<!-- Error associations -->
<FieldText
  id="email"
  label="Email"
  :error="errors.email"
  aria-describedby="email-hint"
/>
<div id="email-hint">We'll never share your email</div>
```

## 📱 Responsive Design

### Breakpoints

- **Mobile**: < 640px
- **Tablet**: 641px - 1024px
- **Desktop**: > 1024px

### Mobile Optimizations

- Touch-friendly 44px minimum targets
- Optimized keyboard layouts (`inputmode="numeric"` for OTP)
- Safe area padding for notched devices
- Reduced spacing on small screens

## 🧪 Testing Considerations

### Test Selectors

All interactive components include `data-test` attributes:

```javascript
// Cypress tests
cy.get('[data-test="login-button"]').click();
cy.get('[data-test="email-field"]').type('user@example.com');
```

### Accessibility Testing

```javascript
// Include in your test suite
describe('Auth Accessibility', () => {
  it('should be keyboard navigable', () => {
    cy.get('input[type="email"]').focus().tab().should('have.attr', 'type', 'password');
  });
  
  it('should announce errors to screen readers', () => {
    cy.get('[role="alert"]').should('exist');
  });
});
```

## 🎨 Theming Examples

### Brand Customization

```css
/* Blue theme */
:root {
  --brand: hsl(217 91% 60%);
  --brand-foreground: hsl(0 0% 100%);
}

/* Green theme */
:root {
  --brand: hsl(142 76% 36%);
  --brand-foreground: hsl(0 0% 100%);
}

/* Purple theme */
:root {
  --brand: hsl(263 70% 60%);
  --brand-foreground: hsl(0 0% 100%);
}
```

### Corporate Theme

```css
:root {
  --brand: hsl(220 100% 25%);        /* Navy blue */
  --brand-foreground: hsl(0 0% 100%); /* White */
  --radius: 0.25rem;                  /* Sharp corners */
  --auth-card-width: 28rem;           /* Wider cards */
}
```

### Soft Theme

```css
:root {
  --brand: hsl(330 100% 70%);        /* Pink */
  --brand-foreground: hsl(0 0% 100%); /* White */
  --radius: 1rem;                     /* Rounded corners */
  --shadow-md: 0 10px 25px -3px rgba(0, 0, 0, 0.1); /* Soft shadows */
}
```

## 📋 QA Checklist

### Accessibility
- [ ] All forms are keyboard navigable
- [ ] Focus indicators are visible
- [ ] Screen reader announcements work
- [ ] Color contrast meets WCAG AA
- [ ] Error messages are properly associated

### Responsive Design
- [ ] Mobile viewport works (320px+)
- [ ] Touch targets are 44px minimum
- [ ] No horizontal scrolling on mobile
- [ ] Cards adapt to screen size
- [ ] Typography scales appropriately

### Dark Mode
- [ ] All colors work in dark mode
- [ ] Logo visibility in both modes
- [ ] Input fields remain readable
- [ ] Focus states are visible

### Browser Support
- [ ] Chrome/Edge (latest 2 versions)
- [ ] Firefox (latest 2 versions)
- [ ] Safari (latest 2 versions)
- [ ] Mobile Safari (iOS 14+)
- [ ] Chrome Mobile (Android 10+)

### Error Handling
- [ ] Field-level errors display correctly
- [ ] Form-level errors are centralized
- [ ] Error states are visually distinct
- [ ] Success states provide feedback

### Loading States
- [ ] Buttons show loading spinners
- [ ] Forms prevent double submission
- [ ] Loading text is accessible
- [ ] Timeouts are handled gracefully

## 🚀 Performance Tips

### Component Optimization

```vue
<!-- Lazy load non-critical components -->
<script setup>
const SocialLoginRow = defineAsyncComponent(() => 
  import('@/components/molecules/SocialLoginRow.vue')
);
</script>

<!-- Use v-show for conditional rendering -->
<div v-show="showRecoveryInput">
  <FieldText label="Recovery Code" />
</div>
```

### CSS Optimization

```css
/* Reduce repaints with transform instead of layout properties */
.button-hover {
  transform: translateY(-1px);
  transition: transform 0.15s ease;
}

/* Use contain for isolated components */
.auth-card {
  contain: layout style paint;
}
```

## 🔗 Integration with Backend

### Laravel Fortify Compatibility

The components are designed to work seamlessly with Laravel Fortify:

```php
// routes/web.php (unchanged)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    // ... other routes remain the same
});
```

### Inertia.js Form Binding

```vue
<Form v-bind="AuthenticatedSessionController.store.form()" v-slot="{ errors, processing }">
  <!-- Components automatically inherit Inertia form state -->
</Form>
```

### Custom Validation

```php
// App/Http/Requests/Auth/LoginRequest.php (unchanged)
public function rules()
{
    return [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ];
}
```

## 🆘 Troubleshooting

### Common Issues

**Components not styled correctly**
- Ensure `tokens.css` is imported
- Check CSS import order
- Verify CSS custom property support

**Dark mode not working**
- Check system preferences
- Verify CSS custom properties in dark mode
- Test with manual `data-theme` attribute

**Forms not submitting**
- Verify Inertia form binding
- Check network requests in DevTools
- Ensure Laravel routes are unchanged

**Accessibility warnings**
- Run `axe-core` accessibility tests
- Check for missing `aria-label` attributes
- Verify keyboard navigation flow

### Performance Issues

**Large bundle size**
- Use dynamic imports for non-critical components
- Remove unused CSS with PurgeCSS
- Optimize SVG icons

**Slow rendering**
- Check for unnecessary reactive dependencies
- Use `v-once` for static content
- Profile with Vue DevTools

## 📞 Support

For questions or issues with this auth UI system:

1. Check this documentation first
2. Review the component source code
3. Test with the provided examples
4. Check accessibility with screen readers
5. Verify responsive design on actual devices

The system is designed to be maintainable and extensible while preserving the existing Laravel/Inertia.js architecture.
