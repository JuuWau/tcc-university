<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                {{ title }}
            </h1>

            <p
                v-if="description"
                class="mt-1 text-sm text-gray-500"
            >
                {{ description }}
            </p>
        </div>

        <div
            v-if="$slots.actions"
            class="flex items-center gap-2"
        >
            <slot name="actions" />
        </div>
    </div>
</template>