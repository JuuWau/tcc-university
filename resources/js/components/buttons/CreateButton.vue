<!-- BaseButton.vue -->
<template>
    <div class="flex justify-start mb-4">
        <button :id="id" type="button" class="text-white bg-blue-500 hover:bg-blue-700 font-medium rounded px-5 py-2 me-2 mb-2 focus:outline-none flex items-center" @click="$emit('click')">
            <i v-if="icon" :class="icon + ' mr-2'"></i>{{ label }}
        </button>
    </div>
</template>

<script setup lang="ts">
const props = defineProps({
    id: { type: String, default: null },
    icon: { type: String, default: 'fas fa-plus' },
    label: { type: String, default: 'Criar' }
})
</script>
