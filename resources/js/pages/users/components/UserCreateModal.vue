<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { UserCreateKey, RefreshTableKey } from '@/keys/users/userKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
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
    const roles = (page.props.roles as Array<{ id: number; name: string; slug: string }>) ?? [];
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Novo Usuário</h2>
            <hr />

            <div class="py-4">
                <label
                    for="user-name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Nome completo (*)
                </label>
                <input
                    id="user-name"
                    type="text"
                    v-model="form.name"
                    maxlength="255"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Nome do usuário"
                />
            </div>

            <div class="py-4">
                <label
                    for="user-email"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Email (*)
                </label>
                <input
                    id="user-email"
                    type="email"
                    v-model="form.email"
                    maxlength="255"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="email@exemplo.com"
                />
            </div>

            <div class="py-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Perfil (*)
                </label>
                <Multiselect
                    v-model="form.role_id"
                    :options="rolesOptions"
                    label="label"
                    value-prop="value"
                    :searchable="true"
                    :close-on-select="true"
                    :can-clear="true"
                    placeholder="Selecione o perfil"
                />
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
