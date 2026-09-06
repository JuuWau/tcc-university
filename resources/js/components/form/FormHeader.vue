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

                <p v-if="subtitle" class="mt-1 text-sm text-gray-500">
                        {{ subtitle }}
                </p>
        </div>
</template>