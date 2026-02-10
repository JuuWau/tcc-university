<template>
    <div>
        <p class="text-xs tracking-wide text-gray-400 uppercase">
            {{ label }}
        </p>
        <p class="mt-1 text-sm font-medium text-gray-900">
            {{ value != null ? value : '—' }}
        </p>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    label: string;
    value?: string | number | null;
}>();
</script>
