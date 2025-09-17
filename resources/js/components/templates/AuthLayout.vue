<template>
  <div class="auth-layout">
    <div class="auth-container">
      <!-- Main Content -->
      <main class="auth-main" role="main">
        <AuthCard :variant="cardVariant" :padding="cardPadding" :max-width="cardMaxWidth">
          <!-- Header Section -->
          <AuthHeader
            v-if="showHeader"
            :title="title"
            :subtitle="description"
            :logo-src="logoSrc"
            :logo-alt="logoAlt"
            :logo-href="logoHref"
            :show-logo="showLogo"
            :title-size="titleSize"
          >
            <template v-if="$slots.logo" #logo>
              <slot name="logo" />
            </template>

            <template v-if="$slots.header" #default>
              <slot name="header" />
            </template>
          </AuthHeader>

          <!-- Main Form Content -->
          <div v-if="showHeader" class="mt-8">
            <slot />
          </div>
          <div v-else>
            <slot />
          </div>
        </AuthCard>
      </main>

      <!-- Optional Footer -->
      <footer v-if="showFooter" class="auth-footer">
        <div class="footer-content">
          <slot name="footer">
            <p class="text-xs text-[var(--muted-foreground)]">
              © {{ currentYear }} {{ footerText }}. All rights reserved.
            </p>
          </slot>
        </div>
      </footer>
    </div>

    <!-- Background Pattern (Optional) -->
    <div v-if="showBackgroundPattern" class="auth-background" aria-hidden="true">
      <slot name="background" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AuthCard from '../organisms/AuthCard.vue';
import AuthHeader from '../organisms/AuthHeader.vue';

interface Props {
  title?: string;
  description?: string;
  logoSrc?: string;
  logoAlt?: string;
  logoHref?: string;
  showLogo?: boolean;
  showHeader?: boolean;
  showFooter?: boolean;
  showBackgroundPattern?: boolean;
  footerText?: string;
  titleSize?: 'sm' | 'md' | 'lg' | 'xl';
  cardVariant?: 'default' | 'elevated' | 'outlined';
  cardPadding?: 'sm' | 'md' | 'lg' | 'xl';
  cardMaxWidth?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
}

const props = withDefaults(defineProps<Props>(), {
  showLogo: true,
  showHeader: true,
  showFooter: true,
  showBackgroundPattern: false,
  footerText: 'Your Company',
  titleSize: 'lg',
  cardVariant: 'default',
  cardPadding: 'lg',
  cardMaxWidth: 'sm',
  logoHref: '/',
  logoAlt: 'Company Logo',
});

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped>
/* Main layout container */
.auth-layout {
  min-height: 100vh;
  min-height: 100svh;
  background: var(--bg);
  color: var(--fg);
  position: relative;
  overflow-x: hidden;
}

/* Content container with centering */
.auth-container {
  min-height: 100vh;
  min-height: 100svh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  position: relative;
  z-index: 10;
}

/* Main content area */
.auth-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: 100%;
}

/* Footer styling */
.auth-footer {
  margin-top: 2rem;
  padding-top: 1rem;
}

.footer-content {
  text-align: center;
  max-width: 24rem;
}

/* Background pattern container */
.auth-background {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
  opacity: 0.05;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .auth-container {
    padding: 1rem;
    min-height: 100vh;
    min-height: 100svh;
  }

  .auth-footer {
    margin-top: 1rem;
  }

  .footer-content {
    max-width: 20rem;
  }
}

/* Tablet adjustments */
@media (min-width: 641px) and (max-width: 1024px) {
  .auth-container {
    padding: 2rem;
  }
}

/* Large screen adjustments */
@media (min-width: 1025px) {
  .auth-container {
    padding: 3rem;
  }
}

/* Landscape mobile adjustments */
@media (max-height: 600px) and (orientation: landscape) {
  .auth-container {
    justify-content: flex-start;
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  .auth-footer {
    margin-top: 1rem;
  }
}

/* Focus management */
.auth-layout:focus-within {
  outline: none;
}

/* High contrast support */
@media (prefers-contrast: high) {
  .auth-layout {
    background: Canvas;
    color: CanvasText;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Print styles */
@media print {
  .auth-layout {
    background: white;
    color: black;
    min-height: auto;
  }

  .auth-container {
    min-height: auto;
    padding: 1rem;
  }

  .auth-footer {
    page-break-inside: avoid;
  }

  .auth-background {
    display: none;
  }
}

/* Safe area adjustments for mobile devices */
.auth-container {
  padding-left: max(1.5rem, env(safe-area-inset-left));
  padding-right: max(1.5rem, env(safe-area-inset-right));
  padding-top: max(1.5rem, env(safe-area-inset-top));
  padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
}

@media (max-width: 640px) {
  .auth-container {
    padding-left: max(1rem, env(safe-area-inset-left));
    padding-right: max(1rem, env(safe-area-inset-right));
    padding-top: max(1rem, env(safe-area-inset-top));
    padding-bottom: max(1rem, env(safe-area-inset-bottom));
  }
}
</style>
