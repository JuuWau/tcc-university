<script setup lang="ts">
import Multiselect from '@vueform/multiselect';

defineOptions({
    inheritAttrs: false,
});

withDefaults(
    defineProps<{
        fieldLabel?: string;
        showLabel?: boolean;
        required?: boolean;
        error?: string;
    }>(),
    {
        fieldLabel: undefined,
        showLabel: true,
        required: false,
        error: '',
    },
);
</script>

<template>
    <div>
        <label
            v-if="showLabel && fieldLabel"
            class="mb-1 block text-sm font-medium text-gray-700"
        >
            {{ fieldLabel }}

            <span
                v-if="required"
                class="text-red-500"
            >
                *
            </span>
        </label>

        <Multiselect
            v-bind="$attrs"
            :required="required"
            :no-options-text="'Nenhuma opção disponível'"
            :no-results-text="'Nenhum resultado encontrado'"
            :class="{ 'multiselect-error': error }"
        />

        <p
            v-if="error"
            class="mt-1 text-sm text-red-600"
        >
            {{ error }}
        </p>
    </div>
</template>