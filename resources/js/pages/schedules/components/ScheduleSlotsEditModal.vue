<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ScheduleSlotEditMultipleKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import { AppPageProps } from '@/types';
import { OpenClinicScheduleClinic, OpenClinicSchedulePeriodOption, OpenClinicScheduleResponsibleOption, OpenClinicScheduleRow, OpenClinicSchedulesFilters } from '@/types/schedule/openClinicSchedules';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, inject, ref } from 'vue';
import { toast } from 'vue3-toastify';
import { scheduleSlotsUpdateSchema } from '@/schemas/scheduleSlotsUpdate.schema';
import { Switch } from '@headlessui/vue';
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import FormHeader from '@/components/form/FormHeader.vue';

type Page = AppPageProps<{
    clinic: OpenClinicScheduleClinic;
    periods: OpenClinicSchedulePeriodOption[];
    slots: OpenClinicScheduleRow[];
    responsible: OpenClinicScheduleResponsibleOption[];
    filters: OpenClinicSchedulesFilters;
}>;
const form = ref({
    responsible_ids: [] as number | [],
    start_time: '',
    end_time: '',
    available_slots: '' as string | number,
    allow_student_booking: false,
    allow_student_enrollment: false,
    allow_procedure_booking: false,
});

const editMultipleModalInjected = inject(ScheduleSlotEditMultipleKey);
const loading = inject(LoadingKey);

if (!editMultipleModalInjected) {
    throw new Error(
        'ScheduleSlotsEditModal precisa estar dentro do provider',
    );
}

const page = usePage<Page>();

const responsibleOptions = computed(() =>
    page.props.responsible.map((r) => ({ label: r.label, value: r.id })),
);

const editMultipleModal = editMultipleModalInjected;

    console.log(editMultipleModal.slots.value)
function close() {
    editMultipleModal.isOpen.value = false;
}

async function submit() {
    const slots = editMultipleModal.slots.value;
    if (!slots?.length || loading?.value) return;

    try {
        if (loading) loading.value = true;

        const payload = {
        ids: slots.map(slot => slot.id),
        slots_data: slots.map(slot => ({ 
                id: slot.id, 
                date: slot.date 
            })),
        ...form.value
        };

        const result = scheduleSlotsUpdateSchema.safeParse(payload);

        if (!result.success) {
        const errors = result.error.errors.map(e => e.message).join('\n');
        toast.error(errors);
        return;
        }

        const { slots_data, ...dataToSend } = result.data;
        
        await axios.put('/schedules/multiple-slots', dataToSend);
        toast.success('Agendas atualizadas com sucesso');
        close();

        router.reload({
        preserveUrl: true,
        });
    } catch (error: any) {
        toast.error(error.response?.data?.message ?? 'Erro ao editar agenda');
    } finally {
        if (loading) loading.value = false;
    }
}
</script>

<template>
	<div
		v-if="editMultipleModal.isOpen.value"
		class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
	>
		<div
			class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg bg-white shadow"
		>
			<FormHeader
				title="Editar agendas"
				subtitle="Atualize as informações das agendas selecionadas."
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
						:can-clear="true"
						:append-to-body="true"
						placeholder="Selecione os responsáveis"
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
						v-model="form.available_slots"
						label="Vagas disponíveis (*)"
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