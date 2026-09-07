<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { ScheduleSlotEditKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { scheduleSlotUpdateSchema } from '@/schemas/scheduleSlotUpdate.schema';
import type { AppPageProps } from '@/types/index';
import type { OpenClinicScheduleClinic, OpenClinicScheduleResponsibleOption, OpenClinicScheduleRow, OpenClinicSchedulesFilters } from '@/types/schedule/openClinicSchedules';
import { Switch } from '@headlessui/vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, reactive, watch } from 'vue';
import { toast } from 'vue3-toastify';

type Page = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;

const editModal = inject(ScheduleSlotEditKey);
const loading = inject(LoadingKey);

if (!editModal) {
    throw new Error('ScheduleSlotEditModal precisa estar dentro do provider');
}

const page = usePage<Page>();

const responsibleOptions = computed(() =>
    page.props.responsible.map((r) => ({ label: r.label, value: r.id })),
);

const form = reactive({
    responsible_ids: [] as number[],
    date: '',
    start_time: '',
    end_time: '',
    available_slots: '' as string | number,
    allow_student_booking: false,
    allow_student_enrollment: false,
    allow_procedure_booking: false,
});

function timeToInput(value: string): string {
    return String(value).slice(0, 5);
}

watch(
    () => editModal.row.value,
    (row: OpenClinicScheduleRow | null) => {
        if (!row) return;
        form.responsible_ids = Array.isArray(row.responsible_ids)
            ? [...row.responsible_ids]
            : [];
        form.date = String(row.date).slice(0, 10);
        form.start_time = timeToInput(row.start_time);
        form.end_time = timeToInput(row.end_time);
        form.available_slots = row.available_slots;
        form.allow_student_booking = row.allow_student_booking;
        form.allow_student_enrollment = row.allow_student_enrollment;
        form.allow_procedure_booking = row.allow_procedure_booking;

        console.log('editModal.row.value', editModal.row.value);
    },
    { immediate: true },
);

function close() {
    editModal.isOpen.value = false;
}

async function submit() {
    const row = editModal.row.value;
    if (!row || loading?.value) return;
    const result = scheduleSlotUpdateSchema.safeParse({
        period_id: row.period_id,
        responsible_ids: form.responsible_ids,
        date: form.date,
        start_time: form.start_time,
        end_time: form.end_time,
        available_slots: form.available_slots,
        allow_student_booking: form.allow_student_booking,
        allow_student_enrollment: form.allow_student_enrollment,
        allow_procedure_booking: form.allow_procedure_booking,
    });

    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    try {
        if (loading) loading.value = true;
        await axios.patch(`/schedules/slots/${row.id}`, {
            period_id: result.data.period_id,
            responsible_ids: form.responsible_ids,
            date: result.data.date,
            start_time: result.data.start_time,
            end_time: result.data.end_time,
            available_slots: result.data.available_slots,
            allow_student_booking: result.data.allow_student_booking,
            allow_student_enrollment: result.data.allow_student_enrollment,
            allow_procedure_booking: result.data.allow_procedure_booking,
        });
        toast.success('Agenda atualizada com sucesso');
        close();
        router.reload({ preserveUrl: true });
    } catch (error: any) {
        const err = error.response?.data;
        if (err?.conflict) {
            toast.error(
                `${err.message ?? 'Conflito de agenda.'} (${err.conflict.date} ${err.conflict.start_time}–${err.conflict.end_time})`,
            );
            return;
        }
        toast.error(err?.message ?? 'Erro ao atualizar agenda');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="editModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar agenda"
				subtitle="Atualize os dados e as opções da agenda."
			/>

			<div class="min-h-0 flex-1 overflow-y-auto px-6">
				<div class="space-y-5 py-5">
					<AppMultiselect
						v-model="form.responsible_ids"
						:options="responsibleOptions"
						field-label="Responsáveis"
						label="label"
						value-prop="value"
						mode="tags"
						:searchable="true"
						:close-on-select="true"
						:can-clear="false"
						:append-to-body="true"
						placeholder="Selecione os responsáveis"
					/>

					<BaseInput
						v-model="form.date"
						label="Data (*)"
						type="date"
					/>

					<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
						<BaseInput
							v-model="form.start_time"
							label="Início (*)"
							type="text"
							v-mask="'##:##'"
							placeholder="HH:mm"
						/>

						<BaseInput
							v-model="form.end_time"
							label="Fim (*)"
							type="text"
							v-mask="'##:##'"
							placeholder="HH:mm"
						/>
					</div>

					<BaseInput
						v-model.number="form.available_slots"
						label="Vagas disponíveis"
						type="number"
						min="0"
						step="1"
						placeholder="Ex: 6"
					/>

					<div class="space-y-3 border-t border-gray-200 pt-4">
						<div
							class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4"
						>
							<div class="min-w-0">
								<p class="text-sm font-medium text-gray-800">
									Permitir inscrição de alunos
								</p>

								<p class="mt-1 text-xs leading-relaxed text-gray-500">
									Se desativado, a ocupação das vagas deverá ser
									gerenciada manualmente pela equipe da clínica.
								</p>
							</div>

							<Switch
								v-model="form.allow_student_booking"
								:class="[
									form.allow_student_booking
										? 'bg-sky-600'
										: 'bg-gray-300',
									'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors',
								]"
							>
								<span
									:class="[
										form.allow_student_booking
											? 'translate-x-6'
											: 'translate-x-1',
										'inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform',
									]"
								/>
							</Switch>
						</div>

						<div
							class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4"
						>
							<div class="min-w-0">
								<p class="text-sm font-medium text-gray-800">
									Inscrever automaticamente os alunos do período
								</p>

								<p class="mt-1 text-xs leading-relaxed text-gray-500">
									Os alunos do período selecionado serão inscritos
									automaticamente nos horários da agenda.
								</p>
							</div>

							<Switch
								v-model="form.allow_student_enrollment"
								:class="[
									form.allow_student_enrollment
										? 'bg-sky-600'
										: 'bg-gray-300',
									'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors',
								]"
							>
								<span
									:class="[
										form.allow_student_enrollment
											? 'translate-x-6'
											: 'translate-x-1',
										'inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform',
									]"
								/>
							</Switch>
						</div>

						<div
							class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4"
						>
							<div class="min-w-0">
								<p class="text-sm font-medium text-gray-800">
									Permitir registro de procedimento
								</p>

								<p class="mt-1 text-xs leading-relaxed text-gray-500">
									Permite que os alunos registrem procedimentos no
									agendamento do paciente.
								</p>
							</div>

							<Switch
								v-model="form.allow_procedure_booking"
								:class="[
									form.allow_procedure_booking
										? 'bg-sky-600'
										: 'bg-gray-300',
									'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full transition-colors',
								]"
							>
								<span
									:class="[
										form.allow_procedure_booking
											? 'translate-x-6'
											: 'translate-x-1',
										'inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform',
									]"
								/>
							</Switch>
						</div>
					</div>
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
