<script setup lang="ts">

defineProps<{
        title: string;
        subtitle?: string;
}>();

</script>

<template>
        <div class="shrink-0 border-b border-gray-200 bg-white px-6 py-4">
                <h2 class="text-lg font-bold text-gray-900">
                        {{ title }}
                </h2>

                <h4 v-if="subtitle" class="text-sm text-gray-500">
                        {{ subtitle }}
                </h4>
        </div>
</template>