<template>
  <AppLayout>
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <Link href="/costing-sheets" class="text-gray-400 hover:text-gray-600">&larr;</Link>
        <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? `Edit — ${sheet.reference}` : 'New Costing Sheet' }}</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Project Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-4">Project Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Project Name *</label>
              <input v-model="form.project_name" class="input" required />
              <p v-if="errors.project_name" class="err">{{ errors.project_name }}</p>
            </div>
            <div>
              <label class="label">Date *</label>
              <input v-model="form.date" type="date" class="input" required />
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
            <div class="sm:col-span-2">
              <label class="label">Notes</label>
              <textarea v-model="form.notes" class="input" rows="2"></textarea>
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-4">Line Items</h2>
          <p v-if="errors.items" class="err mb-3">{{ errors.items }}</p>
          <LineItemsTable :items="form.items" :editable="true" @remove="removeItem" />
          <div class="mt-3 flex gap-2">
            <button type="button" @click="addItem('material')" class="btn-outline text-orange-700 border-orange-300 hover:border-orange-500">+ Material</button>
            <button type="button" @click="addItem('labor')" class="btn-outline text-teal-700 border-teal-300 hover:border-teal-500">+ Labor</button>
          </div>
        </div>

        <!-- Cost Adjustments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="font-semibold text-gray-800 mb-4">Cost Adjustments</h2>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
              <label class="label">Overhead (%)</label>
              <input v-model.number="form.overhead_percent" type="number" min="0" max="100" step="0.01" class="input" />
            </div>
            <div>
              <label class="label">Profit Margin (%)</label>
              <input v-model.number="form.profit_margin_percent" type="number" min="0" max="100" step="0.01" class="input" />
            </div>
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
            <CostSummary :summary="computedSummary" />
          </div>
        </div>

        <div class="flex gap-3 justify-end">
          <Link href="/costing-sheets" class="btn-outline">Cancel</Link>
          <button type="submit" :disabled="processing" class="btn-primary">
            {{ processing ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
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
import LineItemsTable from '@/Components/LineItemsTable.vue';
import CostSummary from '@/Components/CostSummary.vue';

const props = defineProps({ sheet: Object });
const isEdit = computed(() => !!props.sheet?.id);
const errors = ref({});
const processing = ref(false);

const today = new Date().toISOString().slice(0, 10);

const form = reactive({
  project_name: props.sheet?.project_name ?? '',
  client_name: props.sheet?.client_name ?? '',
  client_email: props.sheet?.client_email ?? '',
  client_phone: props.sheet?.client_phone ?? '',
  prepared_by: props.sheet?.prepared_by ?? '',
  date: props.sheet?.date ?? today,
  notes: props.sheet?.notes ?? '',
  overhead_percent: props.sheet?.overhead_percent ?? 0,
  profit_margin_percent: props.sheet?.profit_margin_percent ?? 0,
  discount_percent: props.sheet?.discount_percent ?? 0,
  tax_percent: props.sheet?.tax_percent ?? 0,
  items: props.sheet?.items?.map(i => ({ ...i })) ?? [],
});

const addItem = (type) => {
  form.items.push({ type, description: '', unit: 'pcs', quantity: 1, unit_cost: 0 });
};

const removeItem = (idx) => form.items.splice(idx, 1);

const computedSummary = computed(() => {
  const rawCost = form.items.reduce((s, i) => s + (i.quantity || 0) * (i.unit_cost || 0), 0);
  const overheadAmt = rawCost * (form.overhead_percent / 100);
  const costPlusOverhead = rawCost + overheadAmt;
  const profitAmt = costPlusOverhead * (form.profit_margin_percent / 100);
  const subtotal = costPlusOverhead + profitAmt;
  const discountAmt = subtotal * (form.discount_percent / 100);
  const taxAmt = (subtotal - discountAmt) * (form.tax_percent / 100);

  return {
    raw_cost: rawCost,
    overhead_percent: form.overhead_percent,
    overhead_amount: overheadAmt,
    profit_margin_percent: form.profit_margin_percent,
    profit_amount: profitAmt,
    subtotal,
    discount_percent: form.discount_percent,
    discount_amount: discountAmt,
    tax_percent: form.tax_percent,
    tax_amount: taxAmt,
    total: subtotal - discountAmt + taxAmt,
  };
});

const submit = () => {
  processing.value = true;
  errors.value = {};

  const url = isEdit.value ? `/costing-sheets/${props.sheet.id}` : '/costing-sheets';
  const method = isEdit.value ? 'put' : 'post';

  router[method](url, form, {
    onError: (e) => { errors.value = e; },
    onFinish: () => { processing.value = false; },
  });
};
</script>

