<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { RefreshTableKey, UserCreateKey } from '@/keys/users/userKeys';
import { userSchema } from '@/schemas/user.schema';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { inject, onMounted, reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(UserCreateKey);
const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const loading = inject(LoadingKey);
const rolesOptions = ref<{ label: string; value: number }[]>([]);

const page = usePage();

onMounted(() => {
    const roles =
        (page.props.roles as Array<{
            id: number;
            name: string;
            slug: string;
        }>) ?? [];
    rolesOptions.value = roles.map((r) => ({
        label: r.name,
        value: r.id,
    }));
});

if (!createModal) {
    throw new Error('UserCreateModal precisa estar dentro do provider');
}

const form = reactive({
    name: '' as string | null,
    email: '' as string | null,
    role_id: null as number | null,
});

function close() {
    createModal.isOpen.value = false;
    form.name = null;
    form.email = null;
    form.role_id = null;
}

async function submit() {
    if (loading?.value) return;

    const result = userSchema.safeParse(form);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.post('/users', {
            name: form.name,
            email: form.email,
            role_id: form.role_id,
        });

        toast.success('Convite enviado com sucesso!');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao criar usuário');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="createModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
        >
            <FormHeader
                title="Novo usuário"
                subtitle="Preencha os dados para enviar um convite ao usuário."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <div class="space-y-4 py-4">
                    <BaseInput
                        v-model="form.name"
                        label="Nome completo (*)"
                        type="text"
                        maxlength="255"
                        placeholder="Nome do usuário"
                    />
                    
                    <BaseInput
                        v-model="form.email"
                        label="Email (*)"
                        type="email"
                        maxlength="255"
                        placeholder="email@exemplo.com"
                    />

                    <AppMultiselect
                        v-model="form.role_id"
                        :options="rolesOptions"
                        label="label"
                        field-label="Perfil (*)"
                        value-prop="value"
                        :searchable="true"
                        :close-on-select="true"
                        :can-clear="true"
                        :append-to-body="true"
                        placeholder="Selecione o perfil"
                    />
                </div>
            </div>

            <FormFooter
                :loading="loading"
                action="save"
                action-label="Enviar convite"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>
