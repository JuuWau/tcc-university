<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import FormFooter from '@/components/form/FormFooter.vue';
import FormHeader from '@/components/form/FormHeader.vue';
import { ScheduleSlotAddStudentsKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { OpenClinicScheduleRow } from '@/types/schedule/openClinicSchedules';
import axios from 'axios';
import { X } from 'lucide-vue-next';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

type StudentOption = {
    label: string;
    value: number;
};

const addStudentsModal = inject<any>(ScheduleSlotAddStudentsKey);

if (!addStudentsModal) {
    throw new Error(
        'ScheduleSlotAddStudentsModal precisa estar dentro do provider',
    );
}

const loading = inject<any>(LoadingKey);

const form = reactive({
    students: [] as StudentOption[],
});

const selectedStudent = ref<StudentOption | null>(null);
const confirmRemoveId = ref<number | null>(null);
const studentOptions = ref<StudentOption[]>([]);
const loadingStudents = ref(false);
const loadingSlotStudents = ref(false);

const slots = computed<OpenClinicScheduleRow[]>(
    () => addStudentsModal.slots.value ?? [],
);

const firstSlot = computed(() => slots.value?.[0] ?? null);

const availableStudents = computed(() => {
    return studentOptions.value.filter(
        (option) =>
            !form.students.some((student) => student.value === option.value),
    );
});

watch(
    () => addStudentsModal.isOpen.value,
    async (isOpen: boolean) => {
        if (!isOpen || !firstSlot.value) return;

        resetForm();

        await Promise.all([loadStudents(), loadSlotStudents()]);
    },
);

async function loadStudents() {
    if (!firstSlot.value) return;

    try {
        loadingStudents.value = true;
        console.log('Loading students for period_id:', firstSlot.value.period_id, 'and date:', firstSlot.value.date);
        const { data } = await axios.get('/students/options', {
            params: {
                period_id: firstSlot.value.period_id,
                date: firstSlot.value.date,
            },
        });

        studentOptions.value = data ?? [];
    } catch (error: any) {
        toast.error('Erro ao carregar estudantes');
    } finally {
        loadingStudents.value = false;
    }
}

async function loadSlotStudents() {
    const slotId = firstSlot.value?.id;

    if (!slotId) return;

    try {
        loadingSlotStudents.value = true;

        const { data } = await axios.get(
            `/schedule-enrollment/slots/${slotId}/students`,
        );

        form.students = data ?? [];
    } catch (error: any) {
        toast.error('Erro ao carregar estudantes do agendamento');
    } finally {
        loadingSlotStudents.value = false;
    }
}

function addStudent(student: StudentOption | number | null) {
    if (!student) return;

    const studentObj =
        typeof student === 'number'
            ? studentOptions.value.find((s) => s.value === student)
            : student;

    if (!studentObj) return;

    const alreadyExists = form.students.some(
        (s) => s.value === studentObj.value,
    );

    if (alreadyExists) return;

    form.students.push({
        value: studentObj.value,
        label: studentObj.label,
    });

    selectedStudent.value = null;
}

async function removeStudent(studentId: number) {
    try {
        await axios.delete(
            `/schedule-enrollment/slots/${firstSlot.value.id}/students/${studentId}`,
        );

        form.students = form.students.filter((s) => s.value !== studentId);

        toast.success('Aluno removido com sucesso');
    } catch {
        toast.error('Erro ao remover aluno');
    } finally {
        confirmRemoveId.value = null;
    }
}

function resetForm() {
    form.students = [];
    selectedStudent.value = null;
}

function close() {
    addStudentsModal.isOpen.value = false;

    resetForm();
}

async function submit() {
    if (!slots.value.length || !form.students.length || loading.value) {
        return;
    }

    try {
        loading.value = true;

        await axios.post('/schedule-enrollment/enroll', {
            schedule_slot_ids: slots.value.map((slot) => slot.id),
            student_ids: form.students.map((student) => student.value),
        });

        toast.success('Estudantes adicionados com sucesso');

        await loadSlotStudents();

        close();
    } catch (error: any) {
        toast.error(
            error.response?.data?.message ?? 'Erro ao adicionar estudantes',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="addStudentsModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg bg-white shadow-xl"
        >
            <FormHeader
                title="Adicionar estudantes"
                subtitle="Vincule estudantes aos dias selecionados."
            />

            <div class="min-h-0 flex-1 overflow-y-auto px-6">
                <div class="space-y-5 py-5">
                    <div
                        v-if="firstSlot"
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                    >
                        <div
                            class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2"
                        >
                            <div>
                                <p class="text-xs text-gray-500">Período</p>
                                <p class="font-medium text-gray-800">
                                    {{ firstSlot.period_label }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Horário</p>
                                <p class="font-medium text-gray-800">
                                    {{ firstSlot.start_time.slice(0, 5) }} às
                                    {{ firstSlot.end_time.slice(0, 5) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div>
                            <AppMultiselect
                                v-model="selectedStudent"
                                :options="availableStudents"
                                field-label="Buscar estudantes"
                                label="label"
                                value-prop="value"
                                track-by="value"
                                :searchable="true"
                                :loading="loadingStudents"
                                :can-clear="true"
                                :close-on-select="true"
                                :append-to-body="true"
                                placeholder="Buscar estudante"
                                @select="addStudent"
                            />
                            <p
                                v-if="!availableStudents.length"
                                class="mt-2 text-sm text-gray-500"
                            >
                                Nenhum estudante disponível.
                            </p>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Estudantes inscritos
                                </label>
                                <span
                                    class="inline-flex min-w-6 items-center justify-center rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700"
                                >
                                    {{ form.students.length }}
                                </span>
                            </div>
                            <div
                                class="max-h-64 min-h-50 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3"
                            >
                                <div
                                    v-for="student in form.students"
                                    :key="student.value"
                                    class="mb-2 flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2.5 shadow-sm last:mb-0"
                                >
                                    <span
                                        class="min-w-0 truncate pr-3 text-sm text-gray-700"
                                    >
                                        {{ student.label }}
                                    </span>
                                    <div class="relative shrink-0">
                                        <button
                                            type="button"
                                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                            @click="
                                                confirmRemoveId = student.value
                                            "
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                        <div
                                            v-if="
                                                confirmRemoveId ===
                                                student.value
                                            "
                                            class="absolute top-9 right-0 z-50 w-64 rounded-lg border border-gray-200 bg-white p-3 shadow-lg"
                                        >
                                            <p
                                                class="text-xs leading-relaxed text-gray-700"
                                            >
                                                Isso removerá o aluno e
                                                cancelará todos os agendamentos
                                                dele neste horário.
                                            </p>
                                            <div
                                                class="mt-3 flex justify-end gap-2"
                                            >
                                                <button
                                                    type="button"
                                                    class="cursor-pointer text-xs font-medium text-gray-500 hover:text-gray-700"
                                                    @click="
                                                        confirmRemoveId = null
                                                    "
                                                >
                                                    Cancelar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="cursor-pointer text-xs font-medium text-red-600 hover:text-red-700"
                                                    @click="
                                                        removeStudent(
                                                            student.value,
                                                        )
                                                    "
                                                >
                                                    Confirmar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="loadingSlotStudents"
                                    class="flex h-40 items-center justify-center text-sm text-gray-500"
                                >
                                    Carregando estudantes...
                                </div>
                                <div
                                    v-else-if="!form.students.length"
                                    class="flex h-40 items-center justify-center text-sm text-gray-500"
                                >
                                    Nenhum estudante inscrito.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <FormFooter
                :loading="loading"
                action="save"
                action-label="Adicionar estudantes"
                @cancel="close"
                @save="submit"
            />
        </div>
    </div>
</template>
