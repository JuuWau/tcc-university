<script setup lang="ts">
import type { UserWithInvite } from '@/types/user/user';
import { BadgeMinus, Eye, Mail, UserCheck, UserX } from 'lucide-vue-next';

const props = defineProps<{
    params: {
        data: UserWithInvite;
        onResend?: (user: UserWithInvite) => void;
        onView?: (user: UserWithInvite) => void;
        onDeactivate?: (user: UserWithInvite) => void;
        onActivate?: (user: UserWithInvite) => void;
        onDelete?: (user: UserWithInvite) => void;
    };
}>();

const user = props.params.data;

function status() {
    const invite = user.invite;

    if (user.deleted_at) return 'Inativo';
    if (invite && !invite.used_at) return 'Pendente';
    return 'Ativo';
}
</script>

<template>
    <div class="flex h-full items-center justify-center gap-2">
        <template v-if="status() === 'Pendente'">
            <Mail
                class="cursor-pointer text-blue-500 hover:text-blue-700"
                :size="18"
                title="Reenviar convite"
                @click="params.onResend?.(user)"
            />
            <UserX
                class="cursor-pointer text-red-500 hover:text-red-700"
                :size="18"
                title="Excluir usuário"
                @click="params.onDelete?.(user)"
            />
        </template>

        <template v-else-if="status() === 'Ativo'">
            <Eye
                class="cursor-pointer text-blue-600 hover:text-blue-800"
                :size="18"
                title="Visualizar colaborador"
                @click="params.onView?.(user)"
            />
            <BadgeMinus
                class="cursor-pointer text-yellow-600 hover:text-yellow-800"
                :size="18"
                title="Inativar colaborador"
                @click="params.onDeactivate?.(user)"
            />
        </template>

        <template v-else-if="status() === 'Inativo'">
            <Eye
                class="cursor-pointer text-blue-600 hover:text-blue-800"
                :size="18"
                title="Visualizar colaborador"
                @click="params.onView?.(user)"
            />
            <UserCheck
                class="cursor-pointer text-green-600 hover:text-green-800"
                :size="18"
                title="Ativar colaborador"
                @click="params.onActivate?.(user)"
            />
        </template>

        <template v-else>
            <span class="text-sm text-gray-400">—</span>
        </template>
    </div>
</template>
