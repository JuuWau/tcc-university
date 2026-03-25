<script setup lang="ts">
import { BadgeMinus, Pencil, Trash2, UserCheck } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data?: { active?: boolean } & Record<string, any>;
        onEdit?: (row: any) => void;
        onDeactivate?: (row: any) => void;
        onActivate?: (row: any) => void;
        onDelete?: (row: any) => void;
    };
}>();

function edit() {
    if (!props.params.data) return;
    props.params.onEdit?.(props.params.data);
}

function deactivate() {
    if (!props.params.data) return;
    props.params.onDeactivate?.(props.params.data);
}

function remove() {
    if (!props.params.data) return;
    props.params.onDelete?.(props.params.data);
}

function activate() {
    if (!props.params.data) return;
    props.params.onActivate?.(props.params.data);
}
</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">
        <template v-if="props.params.data?.active">
            <Pencil
                class="cursor-pointer text-blue-500 hover:text-blue-700"
                :size="18"
                title="Editar clínica"
                @click="edit"
            />
            <BadgeMinus
                class="cursor-pointer text-amber-500 hover:text-amber-700"
                :size="18"
                title="Inativar clínica"
                @click="deactivate"
            />
        </template>
        <template v-else>
            <UserCheck
                class="cursor-pointer text-green-600 hover:text-green-800"
                :size="18"
                title="Ativar clínica"
                @click="activate"
            />
        </template>
        <Trash2
            class="cursor-pointer text-red-500 hover:text-red-700"
            :size="18"
            title="Excluir clínica"
            @click="remove"
        />
    </div>
</template>
