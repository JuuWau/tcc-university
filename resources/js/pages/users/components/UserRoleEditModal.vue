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
    rolesList.value.map((r) => ({
        label: r.name,
        value: r.id,
    })),
);

const form = reactive({
    role_id: null as number | null,
});

const errors = reactive({
    role_id: '',
});

const roleInput = ref<{ focus: () => void } | null>(null);

function clearErrors() {
    errors.role_id = '';
}

function focusFirstError(field: string) {
    if (field === 'role_id') {
        roleInput.value?.focus();
    }
}

watch(
    () => editRoleModalOpen.value,
    (isOpen) => {
        if (isOpen && user.value) {
            form.role_id = user.value.roles?.[0]?.id ?? null;
            clearErrors();
        }
    },
);

function close() {
    editRoleModalOpen.value = false;
    clearErrors();
}

async function submit() {
    if (loading.value) return;

    clearErrors();

    const result = userRoleEditSchema.safeParse({
        role_id: form.role_id,
    });

    if (!result.success) {
        result.error.issues.forEach((issue) => {
            const field = issue.path[0] as keyof typeof errors;

            if (field in errors && !errors[field]) {
                errors[field] = issue.message;
            }
        });

        const firstError = result.error.issues[0]?.path[0];

        if (firstError) {
            focusFirstError(String(firstError));
        }

        return;
    }

    try {
        loading.value = true;

        const { data } = await axios.patch<{
            message: string;
            user: UserForTab;
        }>(`/users/${user.value.id}/role`, {
            role_id: form.role_id,
        });

        toast.success(data.message ?? 'Perfil atualizado com sucesso');

        emit('updated');
        close();
    } catch (err: unknown) {
        const message =
            err &&
            typeof err === 'object' &&
            'response' in err
                ? (
                      err as {
                          response?: {
                              data?: {
                                  message?: string;
                              };
                          };
                      }
                  ).response?.data?.message
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

            <form
                class="min-h-0 flex-1 px-6 py-4"
                novalidate
                @submit.prevent="submit"
            >
                <AppMultiselect
                    ref="roleInput"
                    v-model="form.role_id"
                    :options="roleOptions"
                    field-label="Perfil"
                    label="label"
                    value-prop="value"
                    :append-to-body="true"
                    :searchable="true"
                    :close-on-select="true"
                    :can-clear="false"
                    :error="errors.role_id"
                    placeholder="Selecione o perfil"
                    required
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