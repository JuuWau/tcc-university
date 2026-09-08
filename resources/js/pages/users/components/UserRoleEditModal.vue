<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { UserTabContextKey } from '@/keys/users/userKeys';
import { userRoleEditSchema } from '@/schemas/user.schema';
import type { UserForTab } from '@/types/user/user';
import axios from 'axios';
import { computed, inject, reactive, ref, unref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const context = inject(UserTabContextKey);
if (!context) {
    throw new Error('UserRoleEditModal must be used inside UserTab');
}

const user = computed(() => context.user.value);
const editRoleModalOpen = context.editRoleModalOpen;
const rolesList = computed(() => unref(context.roles) ?? []);

const emit = defineEmits<{
    updated: [];
}>();

const loading = ref(false);

const roleOptions = computed(() =>
    rolesList.value.map((r) => ({ label: r.name, value: r.id })),
);

const form = reactive({
    role_id: null as number | null,
});

watch(
    () => editRoleModalOpen.value,
    (isOpen) => {
        if (isOpen && user.value) {
            form.role_id = user.value.roles?.[0]?.id ?? null;
        }
    },
);

function close() {
    editRoleModalOpen.value = false;
}

async function submit() {
    if (loading.value || form.role_id == null) return;

    const result = userRoleEditSchema.safeParse({ role_id: form.role_id });
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;
        const { data } = await axios.patch<{
            message: string;
            user: UserForTab;
        }>(`/users/${user.value.id}/role`, { role_id: form.role_id });
        toast.success(data.message ?? 'Perfil atualizado com sucesso');
        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err && typeof err === 'object' && 'response' in err
                ? (err as { response?: { data?: { message?: string } } })
                      .response?.data?.message
                : null;
        toast.error(message ?? 'Erro ao atualizar perfil');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="editRoleModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Editar perfil do colaborador"
                subtitle="Selecione o novo perfil de acesso do colaborador."
            />
            
            <form class="min-h-0 flex-1 px-6 py-4" @submit.prevent="submit">
                <AppMultiselect
                    v-model="form.role_id"
                    :options="roleOptions"
                    field-label="Perfil (*)"
                    label="label"
                    value-prop="value"
                    :append-to-body="true"
                    :searchable="true"
                    :close-on-select="true"
                    :can-clear="false"
                    placeholder="Selecione o perfil"
                />
            </form>
            
            <FormFooter
                :loading="loading"
                action="save"
                action-label="Salvar"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>
