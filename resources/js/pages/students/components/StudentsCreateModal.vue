<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
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
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Novo aluno"
				subtitle="Preencha os dados para cadastrar um novo aluno."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-4 py-4">
					<BaseInput
						v-model="form.name"
						label="Nome completo (*)"
						type="text"
						maxlength="50"
						placeholder="Escreva o nome completo do aluno"
					/>

					<BaseInput
						v-model="form.registration"
						label="Registro do aluno (*)"
						type="text"
						maxlength="20"
						placeholder="Escreva o registro do aluno"
					/>

					<BaseInput
						v-model="form.email"
						label="Email do aluno (*)"
						type="email"
						maxlength="50"
						placeholder="Escreva o email do aluno"
					/>

					<AppMultiselect
						v-model="form.period"
						:options="periodsOptions"
						field-label="Período (*)"
						label="label"
						value-prop="value"
						:searchable="true"
						:close-on-select="true"
						:can-clear="true"
						:append-to-body="true"
						placeholder="Selecione o período do aluno"
					/>
				</div>
			</div>

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

