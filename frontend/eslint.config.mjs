// @nuxt/eslint generates the base config via `nuxt prepare`; we extend it here.
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt({
  rules: {
    'vue/multi-word-component-names': 'off',
    '@typescript-eslint/no-explicit-any': 'warn',
  },
  ignores: [
    '.output/**',
    '.nuxt/**',
    '.nitro/**',
    'dist/**',
    'node_modules/**',
    'test-results/**',
    'playwright-report/**',
  ],
})
