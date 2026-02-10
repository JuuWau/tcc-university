<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import {
    StudentCreateKey,
    RefreshTableKey,
} from '@/keys/students/studentKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { studentSchema } from '@/schemas/student.schema';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { inject, onMounted, reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject<any>(StudentCreateKey);
const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const loading = inject(LoadingKey);
const periodsOptions = ref<{ label: string; value: number }[]>([]);

const page = usePage();

onMounted(() => {
    const periods = page.props.periods as Array<{
        id: number;
        academic_year: string;
        semester: string;
        calendar_year: string;
    }>;

    periodsOptions.value = periods.map((s) => ({
        label:
            s.academic_year +
            'º ano ' +
            s.semester +
            'º semestre de ' +
            s.calendar_year,
        value: s.id,
    }));
});

if (!createModal) {
    throw new Error('StudentCreateModal precisa estar dentro do provider');
}

const form = reactive({
    name: '' as string | null,
    registration: '' as string | null,
    email: '' as string | null,
    period: null as number | null,
});

function close() {
    createModal.isOpen.value = false;
    form.name = null;
    form.registration = null;
    form.email = null;
    form.period = null;
}

async function submit() {
    if (loading?.value) return;

    const result = studentSchema.safeParse(form);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.post('/students', {
            name: form.name,
            registration: form.registration,
            email: form.email,
            period: form.period,
        });

        toast.success('Enviado convite para o aluno com sucesso!');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao criar período');
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
            <h2 class="mb-4 text-lg font-bold">Novo Aluno</h2>
            <hr />

            <div class="py-4">
                <label
                    for="name"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Nome completo (*)
                </label>

                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    maxlength="50"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Escreva o nome completo do aluno"
                />
            </div>
            <div class="py-4">
                <label
                    for="registration"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Registro do Aluno (*)
                </label>

                <input
                    type="text"
                    v-model="form.registration"
                    maxlength="20"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Escreva o registro do aluno"
                    id="registration"
                />
            </div>
            <div class="py-4">
                <label
                    for="email"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Email do Aluno(*)
                </label>

                <input
                    id="email"
                    type="text"
                    v-model="form.email"
                    maxlength="50"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Escreva o email do aluno"
                />
            </div>

            <div class="py-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Período (*)
                </label>

                <Multiselect
                    v-model="form.period"
                    :options="periodsOptions"
                    label="label"
                    value-prop="value"
                    :searchable="true"
                    :close-on-select="true"
                    :can-clear="true"
                    placeholder="Selecione o período do aluno"
                />
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
