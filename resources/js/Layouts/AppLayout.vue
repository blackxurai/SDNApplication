<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center gap-8">
            <Link href="/" class="text-xl font-bold text-blue-700 tracking-tight">
              SDN Quotation
            </Link>
            <div class="flex gap-1">
              <NavLink href="/costing-sheets" :active="$page.url.startsWith('/costing-sheets')">
                Costing Sheets
              </NavLink>
              <NavLink href="/quotations" :active="$page.url.startsWith('/quotations')">
                Quotations
              </NavLink>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <FlashMessage />
      <slot />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
</script>
