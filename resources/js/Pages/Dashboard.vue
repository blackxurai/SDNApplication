<template>
  <AppLayout>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <StatCard label="Costing Sheets" :value="stats.costingSheets" color="blue" href="/costing-sheets" />
        <StatCard label="Quotations" :value="stats.quotations" color="indigo" href="/quotations" />
        <StatCard label="Accepted Quotes" :value="stats.accepted" color="green" href="/quotations" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-gray-800">Recent Costing Sheets</h2>
            <Link href="/costing-sheets/create" class="btn-primary text-sm">+ New</Link>
          </div>
          <p v-if="recentSheets.length === 0" class="text-gray-400 text-sm py-4 text-center">No costing sheets yet.</p>
          <ul v-else class="divide-y divide-gray-100">
            <li v-for="s in recentSheets" :key="s.id" class="py-3 flex justify-between items-center">
              <div>
                <Link :href="`/costing-sheets/${s.id}`" class="font-medium text-blue-700 hover:underline text-sm">{{ s.reference }}</Link>
                <p class="text-xs text-gray-500">{{ s.project_name }} · {{ s.client_name }}</p>
              </div>
              <StatusBadge :status="s.status" />
            </li>
          </ul>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-gray-800">Recent Quotations</h2>
            <Link href="/quotations" class="text-sm text-blue-600 hover:underline">View all</Link>
          </div>
          <p v-if="recentQuotations.length === 0" class="text-gray-400 text-sm py-4 text-center">No quotations yet.</p>
          <ul v-else class="divide-y divide-gray-100">
            <li v-for="q in recentQuotations" :key="q.id" class="py-3 flex justify-between items-center">
              <div>
                <Link :href="`/quotations/${q.id}`" class="font-medium text-indigo-700 hover:underline text-sm">{{ q.reference }}</Link>
                <p class="text-xs text-gray-500">{{ q.project_name }} · {{ q.client_name }}</p>
              </div>
              <StatusBadge :status="q.status" />
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

defineProps({
  stats: { type: Object, default: () => ({ costingSheets: 0, quotations: 0, accepted: 0 }) },
  recentSheets: { type: Array, default: () => [] },
  recentQuotations: { type: Array, default: () => [] },
});

const StatCard = {
  props: ['label', 'value', 'color', 'href'],
  template: `
    <a :href="href" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow block">
      <p class="text-sm text-gray-500 mb-1">{{ label }}</p>
      <p class="text-3xl font-bold" :class="'text-' + color + '-700'">{{ value }}</p>
    </a>
  `,
};
</script>

