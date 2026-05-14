<template>
  <Link
    :href="href"
    class="px-3 py-2 rounded-md text-sm font-medium transition-colors"
    :class="active
      ? 'bg-blue-50 text-blue-700'
      : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
  >
    <slot />
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ href: String, active: Boolean });
</script>
