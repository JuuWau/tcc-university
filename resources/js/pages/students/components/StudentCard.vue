<script setup lang="ts">
import type { Student } from '@/types/student/student';
import { computed } from 'vue';
import { BadgeMinus, Eye, Mail, UserCheck, UserX } from 'lucide-vue-next';

const props = defineProps<{
    student: Student;
    isAllowedToActivate: boolean;
    isAllowedToDeactivate: boolean;
    isAllowedToDelete: boolean;
    isAllowedToInvite: boolean;
}>();

const emit = defineEmits<{
    edit: [student: Student];
    deactivate: [student: Student];
    activate: [student: Student];
    resend: [student: Student];
    delete: [student: Student];
}>();

const status = computed(() => {
    const invite = props.student.user?.invite;

    if (props.student.deleted_at) return 'Inativo';
    if (invite && !invite.used_at) return 'Pendente';

    return 'Ativo';
});

const statusClasses: Record<string, string> = {
    Ativo: 'bg-green-200 text-green-800',
    Inativo: 'bg-gray-300 text-gray-500',
    Tratamento: 'bg-blue-100 text-blue-800',
    'Pausa no Tratamento': 'bg-amber-100 text-amber-800',
    Abandono: 'bg-red-100 text-red-800',
    Concluído: 'bg-emerald-100 text-emerald-800',
    Transferência: 'bg-violet-100 text-violet-800',
    Pendente: 'bg-yellow-100 text-yellow-800',
    Excluído: 'bg-gray-200 text-gray-600',
};
</script>

<template>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="break-words font-semibold text-gray-900">
                    {{ student.person.name }}
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    RA: {{ student.registration }}
                </p>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
            <span
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                :class="statusClasses[status] ?? 'bg-gray-100 text-gray-800'"
            >
                {{ status }}
            </span>
            <div class="flex items-center gap-4">
                <template v-if="status === 'Pendente'">
                    <Mail
                        v-if="isAllowedToInvite"
                        class="cursor-pointer text-blue-500"
                        :size="20"
                        title="Reenviar email"
                        @click="emit('resend', student)"
                    />

                    <UserX
                        v-if="isAllowedToDelete"
                        class="cursor-pointer text-red-500"
                        :size="20"
                        title="Excluir aluno"
                        @click="emit('delete', student)"
                    />
                </template>

                <template v-else-if="status === 'Ativo'">
                    <Eye
                        class="cursor-pointer text-blue-600"
                        :size="20"
                        title="Ver aluno"
                        @click="emit('edit', student)"
                    />

                    <BadgeMinus
                        v-if="isAllowedToDeactivate"
                        class="cursor-pointer text-yellow-600"
                        :size="20"
                        title="Inativar aluno"
                        @click="emit('deactivate', student)"
                    />
                </template>

                <UserCheck
                    v-else-if="isAllowedToActivate"
                    class="cursor-pointer text-green-600"
                    :size="20"
                    title="Ativar aluno"
                    @click="emit('activate', student)"
                />
            </div>
        </div>
    </div>
</template>
