<script setup lang="ts">
import type { Student } from '@/types/student/student';
import { BadgeMinus, Eye, Mail, UserCheck, UserX } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        isAllowedToActivate: boolean;
        isAllowedToDeactivate: boolean;
        isAllowedToDelete: boolean;
        isAllowedToInvite: boolean;
        data: Student;
        onView?: (student: Student) => void;
        onDeactivate?: (student: Student) => void;
        onActivate?: (student: Student) => void;
        onResend?: (student: Student) => void;
        onDelete?: (student: Student) => void;
    };
}>();

const student = props.params.data;

function status() {
    const invite = student.user?.invite;

    if (student.deleted_at) return 'Inativo';
    if (invite && !invite.used_at) return 'Pendente';
    return 'Ativo';
}
</script>
<template>
    <div class="flex h-full items-center justify-center gap-2">

        <template v-if="status() === 'Pendente'">
            <Mail
                v-if="params.isAllowedToInvite"
                class="cursor-pointer text-blue-500 hover:text-blue-700"
                :size="18"
                title="Reenviar email"
                @click="props.params.onResend?.(student)"
            />

            <UserX
                v-if="params.isAllowedToDelete"
                class="cursor-pointer text-red-500 hover:text-red-700"
                :size="18"
                title="Excluir aluno"
                @click="props.params.onDelete?.(student)"
            />
        </template>

        <template v-else-if="status() === 'Ativo'">
            <Eye
                class="cursor-pointer text-blue-600 hover:text-blue-800"
                :size="18"
                title="Ver aluno"
                @click="props.params.onView?.(student)"
            />

            <BadgeMinus
                v-if="params.isAllowedToDeactivate"
                class="cursor-pointer text-yellow-600 hover:text-yellow-800"
                :size="18"
                title="Inativar aluno"
                @click="props.params.onDeactivate?.(student)"
            />
        </template>

        <UserCheck
            v-else-if="params.isAllowedToActivate"
            class="cursor-pointer text-green-600 hover:text-green-800"
            :size="18"
            title="Ativar aluno"
            @click="props.params.onActivate?.(student)"
        />

    </div>
</template>
