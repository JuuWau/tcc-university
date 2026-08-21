<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    title: string;
    description?: string;
    count: number;
    open: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();
</script>

<template>
    <section
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white"
    >
        <button
            type="button"
            class="flex w-full items-center justify-between px-6 py-4 text-left transition hover:bg-gray-50"
            @click="emit('update:open', !open)"
        >
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-800">
                        {{ title }}
                    </h3>

                    <span
                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500"
                    >
                        {{ count }}
                    </span>
                </div>

                <p
                    v-if="description"
                    class="mt-1 text-sm text-gray-500"
                >
                    {{ description }}
                </p>
            </div>

            <ChevronDown
                class="h-5 w-5 text-gray-400 transition-transform"
                :class="{ 'rotate-180': open }"
            />
        </button>

        <div
            v-if="open"
            class="border-t border-gray-100 p-6"
        >
            <slot />
        </div>
    </section>
</template>