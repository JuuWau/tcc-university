<script setup lang="ts">
import * as Icons from 'lucide-vue-next';
import { computed } from 'vue';

const emit = defineEmits<{
    (e: 'click'): void;
}>();

const props = defineProps<{
    id?: string;
    label?: string;
    icon?: keyof typeof Icons;
}>();

const IconComponent = computed(() => {
    return props.icon ? Icons[props.icon] : null;
});
</script>

<template>
    <div class="flex justify-start">
        <button
            :id="id"
            type="button"
            class="me-2 mb-2 flex items-center rounded bg-blue-400 px-5 py-2 font-medium text-white hover:bg-sky-700 focus:outline-none"
            @click="emit('click')"
        >
            <component
                v-if="IconComponent"
                :is="IconComponent"
                class="mr-2 h-4 w-4 font-bold"
            />

            {{ label ?? 'Criar' }}
        </button>
    </div>
</template>
