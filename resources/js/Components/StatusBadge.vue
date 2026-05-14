<template>
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="classes[status] ?? 'bg-gray-100 text-gray-700'">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({ status: String });

const classes = {
  draft:     'bg-yellow-100 text-yellow-800',
  finalized: 'bg-blue-100 text-blue-800',
  converted: 'bg-purple-100 text-purple-800',
  sent:      'bg-indigo-100 text-indigo-800',
  accepted:  'bg-green-100 text-green-800',
  rejected:  'bg-red-100 text-red-800',
};

const label = computed(() =>
  props.status ? props.status.charAt(0).toUpperCase() + props.status.slice(1) : ''
);
</script>
