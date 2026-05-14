<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex flex-wrap gap-3 justify-between items-start mb-6">
        <div class="flex items-center gap-3">
          <Link href="/quotations" class="text-gray-400 hover:text-gray-600">&larr;</Link>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold text-gray-900 font-mono">{{ quotation.reference }}</h1>
              <StatusBadge :status="quotation.status" />
            </div>
            <p class="text-gray-500 text-sm">{{ quotation.project_name }} · From costing <span class="font-mono">{{ quotation.costing_reference }}</span></p>
          </div>
        </div>

        <div class="flex gap-2 flex-wrap">
          <select v-model="selectedStatus" @change="updateStatus" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
          </select>
          <Link :href="`/quotations/${quotation.id}/edit`" class="btn-outline">Edit</Link>
          <button @click="printQuotation" class="btn-outline">Print / PDF</button>
          <button @click="deleteQuotation" class="btn-outline text-red-600 border-red-300 hover:border-red-500">Delete</button>
        </div>
      </div>

      <!-- Printable quotation -->
      <div id="print-area" class="space-y-5">
        <!-- Client & meta -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-xl font-bold text-gray-900">QUOTATION</h2>
              <p class="font-mono text-lg text-indigo-700">{{ quotation.reference }}</p>
            </div>
            <div class="text-right text-sm text-gray-600">
              <p>Date: <strong>{{ quotation.date }}</strong></p>
              <p v-if="quotation.valid_until">Valid Until: <strong>{{ quotation.valid_until }}</strong></p>
              <p v-if="quotation.prepared_by">Prepared By: <strong>{{ quotation.prepared_by }}</strong></p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Bill To</p>
              <p class="font-semibold text-gray-900">{{ quotation.client_name }}</p>
              <p v-if="quotation.client_email" class="text-gray-600">{{ quotation.client_email }}</p>
              <p v-if="quotation.client_phone" class="text-gray-600">{{ quotation.client_phone }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Project</p>
              <p class="font-semibold text-gray-900">{{ quotation.project_name }}</p>
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-3">Items</h2>
          <LineItemsTable :items="quotation.items" :editable="false" :show-selling="true" />
        </div>

        <!-- Summary + Notes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
            <div v-if="quotation.notes">
              <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Notes</p>
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ quotation.notes }}</p>
            </div>
            <div v-if="quotation.terms">
              <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Terms &amp; Conditions</p>
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ quotation.terms }}</p>
            </div>
            <p v-if="!quotation.notes && !quotation.terms" class="text-sm text-gray-400 italic">No notes or terms added.</p>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-3">Summary</h2>
            <QuotationSummary :quotation="quotation" />
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

const props = defineProps({ quotation: Object });
const selectedStatus = ref(props.quotation.status);

const updateStatus = () => {
  router.patch(`/quotations/${props.quotation.id}/status`, { status: selectedStatus.value });
};

const printQuotation = () => window.print();

const deleteQuotation = () => {
  if (confirm('Delete this quotation? The costing sheet will be set back to "finalized".')) {
    router.delete(`/quotations/${props.quotation.id}`);
  }
};

const currency = (n) => 'RM ' + Number(n ?? 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const QuotationSummary = {
  props: ['quotation'],
  setup(props) {
    return { currency };
  },
  template: `
    <div class="space-y-1 text-sm">
      <div class="flex justify-between text-gray-600">
        <span>Subtotal</span><span>{{ currency(quotation.subtotal) }}</span>
      </div>
      <div v-if="quotation.discount_percent > 0" class="flex justify-between text-red-600">
        <span>Discount ({{ quotation.discount_percent }}%)</span>
        <span>-{{ currency(quotation.discount_amount) }}</span>
      </div>
      <div v-if="quotation.tax_percent > 0" class="flex justify-between text-gray-600">
        <span>Tax ({{ quotation.tax_percent }}%)</span>
        <span>{{ currency(quotation.tax_amount) }}</span>
      </div>
      <div class="flex justify-between text-lg font-bold border-t pt-2 text-indigo-800">
        <span>Total</span><span>{{ currency(quotation.total) }}</span>
      </div>
    </div>
  `,
};
</script>

