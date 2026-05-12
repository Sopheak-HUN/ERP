<script setup lang="ts">
const config = useRuntimeConfig()
const appName = computed(() => config.public.appName)
</script>

<template>
  <div class="min-h-screen flex bg-white dark:bg-[#030711]">
    <!-- Left Side: Brand & Visuals (Hidden on small screens) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-primary-900 overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 z-0 opacity-20 bg-[radial-gradient(#ffffff_1px,transparent_1px)] bg-[size:24px_24px]"></div>
      
      <!-- Content Overlay -->
      <div class="relative z-10 flex flex-col justify-between p-16 text-white w-full">
        <div>
          <div class="flex items-center gap-3 mb-12">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
              <i class="pi pi-shield text-primary-600 text-xl" />
            </div>
            <span class="text-2xl font-bold tracking-tight">{{ appName }}</span>
          </div>
          
          <h2 class="text-5xl font-bold leading-tight mb-6">
            The next generation of <br/>
            <span class="text-primary-400">Enterprise Management.</span>
          </h2>
          <p class="text-lg text-primary-100 max-w-lg leading-relaxed">
            Streamline your operations, manage your resources, and grow your business with our all-in-one ERP solution.
          </p>
        </div>

        <div class="flex flex-col gap-6">
          <div class="flex items-start gap-4">
            <div class="w-6 h-6 rounded-full bg-primary-500/20 flex items-center justify-center mt-1">
              <i class="pi pi-check text-xs text-primary-400" />
            </div>
            <div>
              <p class="font-semibold">Advanced Analytics</p>
              <p class="text-sm text-primary-200">Real-time insights at your fingertips.</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-6 h-6 rounded-full bg-primary-500/20 flex items-center justify-center mt-1">
              <i class="pi pi-check text-xs text-primary-400" />
            </div>
            <div>
              <p class="font-semibold">Secure by Design</p>
              <p class="text-sm text-primary-200">Enterprise-grade security and encryption.</p>
            </div>
          </div>
        </div>

        <p class="text-sm text-primary-300">
          Trusted by leading companies worldwide.
        </p>
      </div>

      <!-- Abstract Glow -->
      <div class="absolute -bottom-20 -right-20 w-[600px] h-[600px] bg-primary-500/20 rounded-full blur-[120px]"></div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8 sm:p-16 relative">
      <div class="absolute top-8 right-8 z-20">
        <ClientOnly>
          <ThemeSwitcher />
        </ClientOnly>
      </div>

      <!-- Mobile Logo -->
      <div class="lg:hidden flex items-center gap-3 mb-12">
        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
          <i class="pi pi-shield text-white text-lg" />
        </div>
        <span class="text-xl font-bold tracking-tight dark:text-white">{{ appName }}</span>
      </div>

      <div class="w-full max-w-[440px]">
        <slot />
      </div>

      <div class="mt-auto pt-12 text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          &copy; {{ new Date().getFullYear() }} {{ appName }} System. All rights reserved. <br/>
          <span class="mt-2 inline-block hover:text-primary-500 cursor-pointer">Terms of Service</span> &bull; 
          <span class="mt-2 inline-block hover:text-primary-500 cursor-pointer ml-1">Privacy Policy</span>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@reference "~/assets/css/main.css";

/* Split screen layout adjustments */
:deep(.p-message) {
  border-radius: 0.75rem;
}
</style>
