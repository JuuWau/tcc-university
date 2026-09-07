<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: boolean;
    label?: string;
    disabled?: boolean;
    required?: boolean;
    error?: string;
    name?: string;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    label: '',
    disabled: false,
    required: false,
    error: '',
    name: undefined,
    id: undefined,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    change: [event: Event];
}>();

const checkboxValue = computed({
    get: () => props.modelValue,
    set: (value: boolean) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="w-full">
        <label
            :for="id"
            :class="[
                'group inline-flex items-center gap-2.5 rounded-lg p-1.5 transition',
                disabled
                    ? 'cursor-not-allowed opacity-60'
                    : 'cursor-pointer hover:bg-gray-50',
            ]"
        >
            <span
                class="relative flex h-5 w-5 shrink-0 items-center justify-center"
            >
                <input
                    :id="id"
                    v-model="checkboxValue"
                    :name="name"
                    type="checkbox"
                    :disabled="disabled"
                    :required="required"
                    class="peer sr-only"
                    @change="emit('change', $event)"
                />

                <span
                    :class="[
                        'flex h-5 w-5 items-center justify-center rounded-md border-2 transition-all duration-150',
                        'peer-focus-visible:ring-2 peer-focus-visible:ring-sky-200 peer-focus-visible:ring-offset-1',
                        'peer-checked:border-sky-600 peer-checked:bg-sky-600',
                        'peer-disabled:cursor-not-allowed peer-disabled:bg-gray-100',
                        error
                            ? 'border-red-400'
                            : 'border-gray-300 group-hover:border-gray-400',
                    ]"
                >
                    <svg
                        v-if="modelValue"
                        viewBox="0 0 12 10"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3 w-3 text-white"
                    >
                        <path
                            d="M1 5L4.2 8L11 1"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>
            </span>

            <span
                v-if="label"
                :class="[
                    'text-sm font-medium text-gray-700 select-none',
                    disabled ? 'cursor-not-allowed' : '',
                ]"
            >
                {{ label }}

                <span v-if="required" class="text-red-500"> * </span>
            </span>
        </label>

        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
