<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <Link :href="`/quotations/${quotation.id}`" class="text-gray-400 hover:text-gray-600">&larr;</Link>
        <h1 class="text-2xl font-bold text-gray-900">Edit Quotation — {{ quotation.reference }}</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-4">Quotation Details</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Project Name *</label>
              <input v-model="form.project_name" class="input" required />
            </div>
            <div>
              <label class="label">Status</label>
              <select v-model="form.status" class="input">
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div>
              <label class="label">Client Name *</label>
              <input v-model="form.client_name" class="input" required />
            </div>
            <div>
              <label class="label">Client Email</label>
              <input v-model="form.client_email" type="email" class="input" />
            </div>
            <div>
              <label class="label">Client Phone</label>
              <input v-model="form.client_phone" class="input" />
            </div>
            <div>
              <label class="label">Prepared By</label>
              <input v-model="form.prepared_by" class="input" />
            </div>
            <div>
              <label class="label">Date *</label>
              <input v-model="form.date" type="date" class="input" required />
            </div>
            <div>
              <label class="label">Valid Until</label>
              <input v-model="form.valid_until" type="date" class="input" />
            </div>
            <div class="sm:col-span-2">
              <label class="label">Notes</label>
              <textarea v-model="form.notes" class="input" rows="2"></textarea>
            </div>
            <div class="sm:col-span-2">
              <label class="label">Terms &amp; Conditions</label>
              <textarea v-model="form.terms" class="input" rows="3"></textarea>
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-4">Line Items</h2>
          <p v-if="errors.items" class="err mb-3">{{ errors.items }}</p>
          <LineItemsTableEditable :items="form.items" @remove="removeItem" />
          <div class="mt-3 flex gap-2">
            <button type="button" @click="addItem('material')" class="btn-outline text-orange-700 border-orange-300">+ Material</button>
            <button type="button" @click="addItem('labor')" class="btn-outline text-teal-700 border-teal-300">+ Labor</button>
          </div>
        </div>

        <!-- Adjustments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-4">Adjustments</h2>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
              <label class="label">Discount (%)</label>
              <input v-model.number="form.discount_percent" type="number" min="0" max="100" step="0.01" class="input" />
            </div>
            <div>
              <label class="label">Tax (%)</label>
              <input v-model.number="form.tax_percent" type="number" min="0" max="100" step="0.01" class="input" />
            </div>
          </div>
          <div class="mt-4">
            <QuotationSummaryLive :items="form.items" :discount="form.discount_percent" :tax="form.tax_percent" />
          </div>
        </div>

        <div class="flex gap-3 justify-end">
          <Link :href="`/quotations/${quotation.id}`" class="btn-outline">Cancel</Link>
          <button type="submit" :disabled="processing" class="btn-primary">
            {{ processing ? 'Saving...' : 'Update Quotation' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({ quotation: Object });
const errors = ref({});
const processing = ref(false);

const form = reactive({
  project_name: props.quotation.project_name ?? '',
  client_name: props.quotation.client_name ?? '',
  client_email: props.quotation.client_email ?? '',
  client_phone: props.quotation.client_phone ?? '',
  prepared_by: props.quotation.prepared_by ?? '',
  date: props.quotation.date ?? '',
  valid_until: props.quotation.valid_until ?? '',
  notes: props.quotation.notes ?? '',
  terms: props.quotation.terms ?? '',
  overhead_percent: props.quotation.overhead_percent ?? 0,
  profit_margin_percent: props.quotation.profit_margin_percent ?? 0,
  discount_percent: props.quotation.discount_percent ?? 0,
  tax_percent: props.quotation.tax_percent ?? 0,
  status: props.quotation.status ?? 'draft',
  items: props.quotation.items?.map(i => ({ ...i })) ?? [],
});

const addItem = (type) => {
  form.items.push({ type, description: '', unit: 'pcs', quantity: 1, unit_cost: 0, selling_price: 0 });
};

const removeItem = (idx) => form.items.splice(idx, 1);

const submit = () => {
  processing.value = true;
  errors.value = {};
  router.put(`/quotations/${props.quotation.id}`, form, {
    onError: (e) => { errors.value = e; },
    onFinish: () => { processing.value = false; },
  });
};

const currency = (n) => 'RM ' + Number(n ?? 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// Inline editable table that includes selling_price column
const LineItemsTableEditable = {
  props: ['items'],
  emits: ['remove'],
  setup() { return { currency }; },
  template: `
    <div>
      <div v-if="items.length === 0" class="text-center py-8 text-gray-400 text-sm border-2 border-dashed rounded-lg">No items.</div>
      <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-3 py-2 text-left">Type</th>
              <th class="px-3 py-2 text-left">Description</th>
              <th class="px-3 py-2 text-left">Unit</th>
              <th class="px-3 py-2 text-right">Qty</th>
              <th class="px-3 py-2 text-right">Cost Price</th>
              <th class="px-3 py-2 text-right">Selling Price</th>
              <th class="px-3 py-2 text-right">Total (Sell)</th>
              <th class="px-3 py-2 w-10"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="(item, idx) in items" :key="idx" class="hover:bg-gray-50">
              <td class="px-3 py-2">
                <select v-model="item.type" class="border border-gray-300 rounded px-2 py-1 text-sm w-24">
                  <option value="material">Material</option>
                  <option value="labor">Labor</option>
                </select>
              </td>
              <td class="px-3 py-2">
                <input v-model="item.description" class="border border-gray-300 rounded px-2 py-1 text-sm w-full min-w-[160px]" />
              </td>
              <td class="px-3 py-2">
                <input v-model="item.unit" class="border border-gray-300 rounded px-2 py-1 text-sm w-20" />
              </td>
              <td class="px-3 py-2">
                <input v-model.number="item.quantity" type="number" min="0" step="0.01" class="border border-gray-300 rounded px-2 py-1 text-sm w-20 text-right" />
              </td>
              <td class="px-3 py-2">
                <input v-model.number="item.unit_cost" type="number" min="0" step="0.01" class="border border-gray-300 rounded px-2 py-1 text-sm w-28 text-right" />
              </td>
              <td class="px-3 py-2">
                <input v-model.number="item.selling_price" type="number" min="0" step="0.01" class="border border-gray-300 rounded px-2 py-1 text-sm w-28 text-right" />
              </td>
              <td class="px-3 py-2 text-right font-medium">{{ currency(item.quantity * item.selling_price) }}</td>
              <td class="px-3 py-2 text-center">
                <button @click="$emit('remove', idx)" class="text-red-400 hover:text-red-600 text-lg">&times;</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  `,
};

const QuotationSummaryLive = {
  props: ['items', 'discount', 'tax'],
  setup(props) {
    const subtotal = computed(() => props.items.reduce((s, i) => s + (i.quantity || 0) * (i.selling_price || 0), 0));
    const discountAmt = computed(() => subtotal.value * ((props.discount || 0) / 100));
    const taxAmt = computed(() => (subtotal.value - discountAmt.value) * ((props.tax || 0) / 100));
    const total = computed(() => subtotal.value - discountAmt.value + taxAmt.value);
    return { subtotal, discountAmt, taxAmt, total, currency };
  },
  template: `
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-1 text-sm">
      <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>{{ currency(subtotal) }}</span></div>
      <div v-if="discount > 0" class="flex justify-between text-red-600"><span>Discount ({{ discount }}%)</span><span>-{{ currency(discountAmt) }}</span></div>
      <div v-if="tax > 0" class="flex justify-between text-gray-600"><span>Tax ({{ tax }}%)</span><span>{{ currency(taxAmt) }}</span></div>
      <div class="flex justify-between font-bold text-lg border-t pt-2 text-indigo-800"><span>Total</span><span>{{ currency(total) }}</span></div>
    </div>
  `,
};
</script>

