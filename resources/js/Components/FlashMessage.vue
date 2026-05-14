<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="visible"
      class="mb-4 px-4 py-3 rounded-lg flex items-center justify-between text-sm font-medium"
      :class="isError ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-green-50 text-green-800 border border-green-200'"
    >
      <span>{{ message }}</span>
      <button @click="visible = false" class="ml-4 opacity-60 hover:opacity-100">&times;</button>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const isError = ref(false);

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      message.value = flash.success;
      isError.value = false;
      visible.value = true;
      setTimeout(() => (visible.value = false), 4000);
    } else if (flash?.error) {
      message.value = flash.error;
      isError.value = true;
      visible.value = true;
    }
  },
  { immediate: true, deep: true }
);
</script>
