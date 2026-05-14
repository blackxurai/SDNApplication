<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex flex-wrap gap-3 justify-between items-start mb-6">
        <div class="flex items-center gap-3">
          <Link href="/costing-sheets" class="text-gray-400 hover:text-gray-600">&larr;</Link>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ sheet.reference }}</h1>
              <StatusBadge :status="sheet.status" />
            </div>
            <p class="text-gray-500 text-sm">{{ sheet.project_name }} · {{ sheet.date }}</p>
          </div>
        </div>

        <div class="flex gap-2 flex-wrap">
          <Link v-if="sheet.status !== 'converted'" :href="`/costing-sheets/${sheet.id}/edit`" class="btn-outline">Edit</Link>

          <button
            v-if="sheet.status === 'draft'"
            @click="finalize"
            class="btn-outline text-blue-700 border-blue-300"
          >
            Finalize
          </button>

          <button
            v-if="sheet.status === 'finalized' && !sheet.quotation"
            @click="convertToQuotation"
            :disabled="converting"
            class="btn-primary"
          >
            {{ converting ? 'Converting...' : 'Convert to Quotation' }}
          </button>

          <Link
            v-if="sheet.quotation"
            :href="`/quotations/${sheet.quotation.id}`"
            class="btn-outline text-purple-700 border-purple-300"
          >
            View Quotation ({{ sheet.quotation.reference }})
          </Link>

          <button
            v-if="sheet.status !== 'converted'"
            @click="deleteSheet"
            class="btn-outline text-red-600 border-red-300 hover:border-red-500"
          >
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-5">
          <!-- Client info -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Client Information</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
              <InfoRow label="Client" :value="sheet.client_name" />
              <InfoRow label="Email" :value="sheet.client_email || '—'" />
              <InfoRow label="Phone" :value="sheet.client_phone || '—'" />
              <InfoRow label="Prepared By" :value="sheet.prepared_by || '—'" />
            </dl>
            <div v-if="sheet.notes" class="mt-3 pt-3 border-t text-sm text-gray-600">
              <p class="text-xs font-medium text-gray-500 mb-1">Notes</p>
              {{ sheet.notes }}
            </div>
          </div>

          <!-- Line Items -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Line Items</h2>
            <LineItemsTable :items="sheet.items" :editable="false" />
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-5">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Cost Summary</h2>
            <CostSummary :summary="sheet" />
          </div>

          <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800" v-if="sheet.status === 'draft'">
            <strong>Draft</strong> — Finalize the sheet before converting it to a quotation.
          </div>
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800" v-if="sheet.status === 'finalized'">
            <strong>Finalized</strong> — Ready to convert to a quotation.
          </div>
          <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-sm text-purple-800" v-if="sheet.status === 'converted'">
            <strong>Converted</strong> — A quotation has been created from this sheet.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import LineItemsTable from '@/Components/LineItemsTable.vue';
import CostSummary from '@/Components/CostSummary.vue';

const props = defineProps({ sheet: Object });
const converting = ref(false);

const finalize = () => router.patch(`/costing-sheets/${props.sheet.id}/finalize`);

const convertToQuotation = () => {
  converting.value = true;
  router.post(`/quotations/from-costing/${props.sheet.id}`, {}, {
    onFinish: () => { converting.value = false; },
  });
};

const deleteSheet = () => {
  if (confirm('Delete this costing sheet? This cannot be undone.')) {
    router.delete(`/costing-sheets/${props.sheet.id}`);
  }
};

const InfoRow = {
  props: ['label', 'value'],
  template: `<div><dt class="text-xs font-medium text-gray-500">{{ label }}</dt><dd class="mt-0.5 text-gray-800">{{ value }}</dd></div>`,
};
</script>

