<script setup lang="ts">
import type { UserWithInvite } from '@/types/user/user';
import { BadgeMinus, Eye, Mail, UserCheck, UserX } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
	user: UserWithInvite;
	onResend?: (user: UserWithInvite) => void;
	onView?: (user: UserWithInvite) => void;
	onDeactivate?: (user: UserWithInvite) => void;
	onActivate?: (user: UserWithInvite) => void;
	onDelete?: (user: UserWithInvite) => void;
}>();

const status = computed(() => {
	const invite = props.user.invite;

	if (props.user.deleted_at) return 'Inativo';
	if (invite && !invite.used_at) return 'Pendente';

	return 'Ativo';
});

const statusClasses: Record<string, string> = {
	Ativo: 'bg-green-200 text-green-800',
	Inativo: 'bg-gray-300 text-gray-500',
	Pendente: 'bg-yellow-100 text-yellow-800',
};
</script>

<template>
	<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
		<div class="flex items-start justify-between gap-3">
			<div class="min-w-0">
				<h3 class="break-words font-semibold text-gray-900">
					{{ user.person?.name }}
				</h3>

				<p class="break-words mt-1 text-sm text-gray-500">
					{{ user.email }}
				</p>

				<p
					v-if="user.role?.name"
					class="mt-1 text-sm text-gray-500"
				>
					Perfil: {{ user.role.name }}
				</p>
			</div>
		</div>

		<div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
			<span
				class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-xs font-semibold"
				:class="
					statusClasses[status] ??
					'bg-gray-100 text-gray-800'
				"
			>
				{{ status }}
			</span>

                        <div class="flex items-center gap-4">
                                <template v-if="status === 'Pendente'">
                                        <Mail
                                                class="cursor-pointer text-blue-500 hover:text-blue-700"
                                                :size="20"
                                                title="Reenviar convite"
                                                @click="onResend?.(user)"
                                        />

                                        <UserX
                                                class="cursor-pointer text-red-500 hover:text-red-700"
                                                :size="20"
                                                title="Excluir usuário"
                                                @click="onDelete?.(user)"
                                        />
                                </template>

                                <template v-else-if="status === 'Ativo'">
                                        <Eye
                                                class="cursor-pointer text-blue-600 hover:text-blue-800"
                                                :size="20"
                                                title="Visualizar colaborador"
                                                @click="onView?.(user)"
                                        />

                                        <BadgeMinus
                                                class="cursor-pointer text-yellow-600 hover:text-yellow-800"
                                                :size="20"
                                                title="Inativar colaborador"
                                                @click="onDeactivate?.(user)"
                                        />
                                </template>

                                <template v-else-if="status === 'Inativo'">
                                        <Eye
                                                class="cursor-pointer text-blue-600 hover:text-blue-800"
                                                :size="20"
                                                title="Visualizar colaborador"
                                                @click="onView?.(user)"
                                        />

                                        <UserCheck
                                                class="cursor-pointer text-green-600 hover:text-green-800"
                                                :size="20"
                                                title="Ativar colaborador"
                                                @click="onActivate?.(user)"
                                        />
                                </template>
                        </div>
		</div>
	</div>
</template>
