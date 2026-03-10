<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { UserTabContextKey } from '@/keys/users/userKeys';
import { userRoleEditSchema } from '@/schemas/user.schema';
import type { UserForTab } from '@/types/user/user';
import Multiselect from '@vueform/multiselect';
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
            form.role_id = user.value.role?.id ?? null;
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
            class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-lg bg-white p-6"
        >
            <h2 class="mb-4 text-lg font-bold">Editar perfil do colaborador</h2>
            <hr />

            <form class="space-y-4 pt-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Perfil (*)
                    </label>
                    <Multiselect
                        v-model="form.role_id"
                        :options="roleOptions"
                        label="label"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="false"
                        placeholder="Selecione o perfil"
                    />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <CancelButton @click="close" />
                    <SaveButton :loading="loading" @click.stop="submit" />
                </div>
            </form>
        </div>
    </div>
</template>
