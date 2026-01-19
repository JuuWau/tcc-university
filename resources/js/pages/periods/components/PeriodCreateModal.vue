<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { PeriodCreateKey, PeriodsGroupKey } from '@/keys/periods/periodKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { periodSchema } from '@/schemas/period.schema';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { inject, onMounted, reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject<any>(PeriodCreateKey);
const periods = inject<any>(PeriodsGroupKey);
const loading = inject(LoadingKey);
const specialtiesOptions = ref<{ label: string; value: number }[]>([]);

const page = usePage();

onMounted(() => {
    const specialties = page.props.specialties as Array<{
        id: number;
        name: string;
    }>;

    specialtiesOptions.value = specialties.map((s) => ({
        label: s.name,
        value: s.id,
    }));
});

if (!createModal) {
    throw new Error('PeriodCreateModal precisa estar dentro do provider');
}

const form = reactive({
    academic_year: '' as string | null,
    semester: '' as string | null,
    calendar_year: '' as string | null,
    specialties: [] as number[],
});

function close() {
    createModal.isOpen.value = false;
    form.academic_year = null;
    form.semester = null;
    form.calendar_year = null;
    form.specialties = [];
}

async function submit() {
    if (loading.value) return;

    const result = periodSchema.safeParse(form);
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        loading.value = true;

        const res = await axios.post('/periods', {
            academic_year: form.academic_year,
            semester: form.semester,
            calendar_year: form.calendar_year,
            specialties: form.specialties,
        });

        periods.value.unshift(res.data.period);
        toast.success('Período criado com sucesso');
        close();
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao criar período');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="createModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6">
            <h2 class="mb-4 text-lg font-bold">Novo Período</h2>
            <hr />

            <div class="py-4">
                <label
                    for="academic_year"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Ano acadêmico (*)
                </label>

                <input
                    id="academic_year"
                    type="text"
                    v-model="form.academic_year"
                    maxlength="1"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Ex: 4º ano"
                />
            </div>
            <div class="py-4">
                <label
                    for="semester"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Semestre (*)
                </label>

                <input
                    type="text"
                    v-model="form.semester"
                    maxlength="1"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Ex: 1º semestre"
                    id="semester"
                />
            </div>
            <div class="py-4">
                <label
                    for="calendar_year"
                    class="mb-2 block text-sm font-medium text-gray-700"
                >
                    Ano calendário (*)
                </label>

                <input
                    id="calendar_year"
                    type="text"
                    v-model="form.calendar_year"
                    maxlength="4"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    class="w-full rounded border px-3 py-2 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 focus:outline-none"
                    placeholder="Ex: 2024"
                />
            </div>

            <div class="py-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Especialidades (*)
                </label>

                <Multiselect
                    v-model="form.specialties"
                    :options="specialtiesOptions"
                    mode="tags"
                    label="label"
                    track-by="value"
                    value-prop="value"
                    placeholder="Selecione as especialidades"
                />
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />
                <SaveButton :loading="loading" @click.stop="submit" />
            </div>
        </div>
    </div>
</template>
