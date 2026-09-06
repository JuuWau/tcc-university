<script setup lang="ts">
import Multiselect from '@vueform/multiselect'

withDefaults(
    defineProps<{
        label?: string
        showLabel?: boolean
    }>(),
    {
        showLabel: true,
    },
)
</script>

<template>
    <div>
        <label
            v-if="showLabel && label"
            class="mb-2 block text-sm font-medium text-gray-700"
        >
            {{ label }}
        </label>

        <Multiselect
            v-bind="$attrs"
            :no-options-text="'Nenhuma opção disponível'"
            :no-results-text="'Nenhum resultado encontrado'"
        />
    </div>
</template>