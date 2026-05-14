<template>
  <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-1 text-sm">
    <div class="flex justify-between text-gray-600">
      <span>Raw Cost</span><span>{{ currency(summary.raw_cost) }}</span>
    </div>
    <div v-if="summary.overhead_percent > 0" class="flex justify-between text-gray-600">
      <span>Overhead ({{ summary.overhead_percent }}%)</span>
      <span>{{ currency(summary.overhead_amount) }}</span>
    </div>
    <div v-if="summary.profit_margin_percent > 0" class="flex justify-between text-gray-600">
      <span>Profit Margin ({{ summary.profit_margin_percent }}%)</span>
      <span>{{ currency(summary.profit_amount) }}</span>
    </div>
    <div class="flex justify-between font-medium border-t pt-1">
      <span>Subtotal</span><span>{{ currency(summary.subtotal) }}</span>
    </div>
    <div v-if="summary.discount_percent > 0" class="flex justify-between text-red-600">
      <span>Discount ({{ summary.discount_percent }}%)</span>
      <span>-{{ currency(summary.discount_amount) }}</span>
    </div>
    <div v-if="summary.tax_percent > 0" class="flex justify-between text-gray-600">
      <span>Tax ({{ summary.tax_percent }}%)</span>
      <span>{{ currency(summary.tax_amount) }}</span>
    </div>
    <div class="flex justify-between text-lg font-bold border-t pt-2 text-blue-800">
      <span>Total</span><span>{{ currency(summary.total) }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({ summary: Object });
const currency = (n) => 'RM ' + Number(n ?? 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
</script>
