<script setup lang="ts">
import { City, IbgeService, Uf } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import PatientForm from '@/components/form/patient/PatientForm.vue';
import { PatientCreateKey, RefreshTableKey } from '@/keys/patients/patientKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { patientCreateSchema } from '@/schemas/patient.schema';
import type { StudentOption } from '@/types/patient/patient';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

const createModal = inject(PatientCreateKey);
const refreshTableRef = inject<{ value: (() => void) | null }>(RefreshTableKey);
const loading = inject(LoadingKey);
const page = usePage();
const viaCep = ViaCep();
const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);
const patientTypeOptions = [
    { label: 'Adulto', value: 'adulto' },
    { label: 'Pediatria', value: 'pediatria' },
];

const biologicalSexOptions = [
    { label: 'Masculino', value: 'male' },
    { label: 'Feminino', value: 'female' },
];

const students = computed(
    () => (page.props as { students?: StudentOption[] }).students ?? [],
);
const studentsOptions = computed(() =>
    students.value.map((s) => ({ label: s.name, value: s.id })),
);
const stateOptions = computed(() =>
    states.value.map((s) => ({ label: s.nome, value: s.sigla })),
);
const cityOptions = computed(() =>
    cities.value.map((c) => ({ label: c.nome, value: c.nome })),
);

if (!createModal) {
    throw new Error('PatientCreateModal precisa estar dentro do provider');
}
const modal = createModal;

const form = reactive({
    code: '' as string | null,
    name: '' as string | null,
    email: '' as string | null,
    student_ids: [] as number[],
    cpf: '' as string | null,
    phone: '' as string | null,
    birth_date: '' as string | null,
    biological_sex: null as 'male' | 'female' | null,
    cep: '' as string | null,
    street: '' as string | null,
    neighborhood: '' as string | null,
    number: '' as string | null,
    complement: null as string | null,
    city: '' as string | null,
    state: '' as string | null,
    patient_type: null as 'adulto' | 'pediatria' | null,
});

watch(
    () => modal.isOpen.value,
    (isOpen) => {
        if (isOpen) {
            void loadStates();
        }
    },
);

async function loadStates() {
    if (states.value.length) return;
    states.value = await IbgeService.getUfData();
}

watch(
    () => form.cep,
    async (newCep) => {
        const cepClean = newCep?.replace(/\D/g, '');
        if (cepClean && cepClean.length === 8) {
            const data = await viaCep.getCepData(cepClean);
            if (!data) return;
            form.street = data.logradouro ?? '';
            form.neighborhood = data.bairro ?? '';
            form.city = data.localidade ?? '';
            form.state = data.uf ?? '';
            if (data.uf) {
                cities.value = await IbgeService.getCityData(data.uf);
            }
        }
    },
);

watch(
    () => form.state,
    async (newState) => {
        if (!newState) {
            cities.value = [];
            form.city = null;
            return;
        }
        cities.value = await IbgeService.getCityData(newState);
        if (!cities.value.some((c) => c.nome === form.city)) {
            form.city = null;
        }
    },
);

function close() {
    modal.isOpen.value = false;
    form.code = '';
    form.name = '';
    form.email = null;
    form.student_ids = [];
    form.cpf = null;
    form.phone = null;
    form.birth_date = null;
    form.biological_sex = null;
    form.cep = null;
    form.street = null;
    form.neighborhood = null;
    form.number = null;
    form.complement = null;
    form.city = null;
    form.state = null;
    form.patient_type = null;
}

async function submit() {
    if (loading?.value) return;

    const validation = patientCreateSchema.safeParse(form);
    if (!validation.success) {
        toast.error(validation.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;

        await axios.post('/patients', validation.data);

        toast.success('Paciente cadastrado com sucesso!');
        close();
        refreshTableRef?.value?.();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao cadastrar paciente',
        );
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="modal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white"
        >
            <FormHeader
                title="Novo Paciente"
                subtitle="Preencha os dados para cadastrar um novo paciente."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <PatientForm
                    v-model="form"
                    :students-options="studentsOptions"
                    :state-options="stateOptions"
                    :city-options="cityOptions"
                />
            </div>

            <FormFooter :loading="loading" @cancel="close" @save="submit" />
        </div>
    </div>
</template>
