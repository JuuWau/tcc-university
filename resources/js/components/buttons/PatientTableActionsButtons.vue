<script setup lang="ts">
import type { PatientWithInvite } from '@/types/patient/patient';
import { BadgeMinus, Eye, Trash2, UserCheck } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        canUpdate: boolean;
        canDelete: boolean;
        data: PatientWithInvite;
        onView?: (patient: PatientWithInvite) => void;
        onDeactivate?: (patient: PatientWithInvite) => void;
        onActivate?: (patient: PatientWithInvite) => void;
        onDelete?: (patient: PatientWithInvite) => void;
    };
}>();

const patient = props.params.data;

function displayStatus(): string {
    if (patient.deleted_at) return 'inativo';
    return (patient.status ?? 'ativo') as string;
}

const isInativo = () => displayStatus() === 'inativo';
const canDeactivate = () => !patient.deleted_at && !isInativo();
</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">
        <Eye
            class="cursor-pointer text-blue-600 hover:text-blue-800"
            :size="18"
            title="Visualizar cadastro"
            @click="params.onView?.(patient)"
        />
        <BadgeMinus
            v-if="canDeactivate() && params.canUpdate"
            class="cursor-pointer text-yellow-600 hover:text-yellow-800"
            :size="18"
            title="Inativar paciente"
            @click="params.onDeactivate?.(patient)"
        />
        <UserCheck
            v-if="isInativo() && params.canUpdate"
            class="cursor-pointer text-green-600 hover:text-green-800"
            :size="18"
            title="Ativar paciente"
            @click="params.onActivate?.(patient)"
        />
        <Trash2
            v-if="isInativo() && params.canDelete"
            class="cursor-pointer text-red-500 hover:text-red-700"
            :size="18"
            title="Excluir paciente"
            @click="params.onDelete?.(patient)"
        />
    </div>
</template>
