<template>
  <AppLayout>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Quotations</h1>
      <Link href="/costing-sheets/create" class="btn-primary">+ New Costing Sheet</Link>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-left">Reference</th>
            <th class="px-4 py-3 text-left">Project</th>
            <th class="px-4 py-3 text-left">Client</th>
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-left">Valid Until</th>
            <th class="px-4 py-3 text-right">Total</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="quotations.length === 0">
            <td colspan="8" class="text-center py-12 text-gray-400">
              No quotations yet. <Link href="/costing-sheets/create" class="text-blue-600 hover:underline">Start from a costing sheet</Link>.
            </td>
          </tr>
          <tr v-for="q in quotations" :key="q.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 font-mono text-indigo-700 font-medium">
              <Link :href="`/quotations/${q.id}`">{{ q.reference }}</Link>
            </td>
            <td class="px-4 py-3 font-medium text-gray-800">{{ q.project_name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ q.client_name }}</td>
            <td class="px-4 py-3 text-gray-500">{{ q.date }}</td>
            <td class="px-4 py-3 text-gray-500">{{ q.valid_until ?? '—' }}</td>
            <td class="px-4 py-3 text-right font-medium text-gray-800">{{ currency(q.total) }}</td>
            <td class="px-4 py-3 text-center"><StatusBadge :status="q.status" /></td>
            <td class="px-4 py-3 text-center">
              <div class="flex justify-center gap-2">
                <Link :href="`/quotations/${q.id}`" class="text-xs text-gray-600 hover:text-gray-900 px-2 py-1 rounded border border-gray-300 hover:border-gray-400 transition-colors">View</Link>
                <Link :href="`/quotations/${q.id}/edit`" class="text-xs text-indigo-600 hover:text-indigo-800 px-2 py-1 rounded border border-indigo-300 hover:border-indigo-400 transition-colors">Edit</Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

defineProps({ quotations: Array });

const currency = (n) => 'RM ' + Number(n ?? 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

