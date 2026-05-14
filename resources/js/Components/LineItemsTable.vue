<template>
  <div>
    <div v-if="items.length === 0" class="text-center py-8 text-gray-400 text-sm border-2 border-dashed rounded-lg">
      No items added yet. Click "Add Item" below.
    </div>

    <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
          <tr>
            <th class="px-3 py-2 text-left">Type</th>
            <th class="px-3 py-2 text-left">Description</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-right">Qty</th>
            <th class="px-3 py-2 text-right">Unit Cost</th>
            <th v-if="showSelling" class="px-3 py-2 text-right">Selling Price</th>
            <th class="px-3 py-2 text-right">{{ showSelling ? 'Total (Sell)' : 'Total' }}</th>
            <th v-if="editable" class="px-3 py-2 w-10"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <tr v-for="(item, idx) in items" :key="idx" class="hover:bg-gray-50">
            <td class="px-3 py-2">
              <template v-if="editable">
                <select v-model="item.type" class="input-sm w-24">
                  <option value="material">Material</option>
                  <option value="labor">Labor</option>
                </select>
              </template>
              <template v-else>
                <span class="px-2 py-0.5 rounded text-xs font-medium"
                  :class="item.type === 'material' ? 'bg-orange-100 text-orange-700' : 'bg-teal-100 text-teal-700'">
                  {{ item.type === 'material' ? 'Material' : 'Labor' }}
                </span>
              </template>
            </td>

            <td class="px-3 py-2">
              <input v-if="editable" v-model="item.description" class="input-sm w-full min-w-[180px]" placeholder="Description" />
              <span v-else>{{ item.description }}</span>
            </td>

            <td class="px-3 py-2">
              <input v-if="editable" v-model="item.unit" class="input-sm w-20" placeholder="pcs" />
              <span v-else>{{ item.unit }}</span>
            </td>

            <td class="px-3 py-2 text-right">
              <input v-if="editable" v-model.number="item.quantity" type="number" min="0" step="0.01" class="input-sm w-24 text-right" />
              <span v-else>{{ fmt(item.quantity) }}</span>
            </td>

            <td class="px-3 py-2 text-right">
              <input v-if="editable" v-model.number="item.unit_cost" type="number" min="0" step="0.01" class="input-sm w-28 text-right" />
              <span v-else>{{ currency(item.unit_cost) }}</span>
            </td>

            <td v-if="showSelling" class="px-3 py-2 text-right">
              <input v-if="editable" v-model.number="item.selling_price" type="number" min="0" step="0.01" class="input-sm w-28 text-right" />
              <span v-else>{{ currency(item.selling_price) }}</span>
            </td>

            <td class="px-3 py-2 text-right font-medium text-gray-800">
              {{ showSelling ? currency(item.quantity * item.selling_price) : currency(item.quantity * item.unit_cost) }}
            </td>

            <td v-if="editable" class="px-3 py-2 text-center">
              <button @click="$emit('remove', idx)" class="text-red-400 hover:text-red-600 text-lg leading-none">&times;</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: { type: Array, default: () => [] },
  editable: { type: Boolean, default: false },
  showSelling: { type: Boolean, default: false },
});
defineEmits(['remove']);

const fmt = (n) => Number(n ?? 0).toLocaleString();
const currency = (n) => 'RM ' + Number(n ?? 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>

