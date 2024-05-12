<script setup lang="ts">
import Select from '@/components/Elements/Select.vue'

interface Props {
  disk?: string;
  disks: string[];
  setDisk: (disk: string) => void;
  isLoading: boolean;
  sortOptions: string[]; // Adjusted to use an array of strings
  selectedSortOption: string;
  setSort: (sort: string) => void;
}

withDefaults(defineProps<Props>(), {
  isLoading: false,
  disks: () => [],
  sortOptions: () => ['name', 'extension', 'size'], // Updated to use an array of strings
  selectedSortOption: 'name' // Default selected sort option
})
</script>

<template>
  <div>
    <!-- Select for sorting -->
    <div class="flex space-x-2 items-center">
      <!-- Select for disk -->
      <label>Disk:</label>
      <Select :current="disk" :on-click="setDisk" :options="disks" :is-loading="isLoading" />

      <label>Sort By:</label>
      <Select :current="selectedSortOption" :on-click="setSort" :options="sortOptions" />
    </div>
  </div>
</template>
