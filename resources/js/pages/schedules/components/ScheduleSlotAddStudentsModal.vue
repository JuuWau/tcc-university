<script setup lang="ts">
import CancelButton from '@/components/buttons/CancelButton.vue';
import SaveButton from '@/components/buttons/SaveButton.vue';
import { ScheduleSlotAddStudentsKey } from '@/keys/schedules/scheduleSlotKeys';
import { LoadingKey } from '@/keys/ui/loadingKey';
import type { OpenClinicScheduleRow } from '@/types/schedule/openClinicSchedules';
import axios from 'axios';
import Multiselect from '@vueform/multiselect';
import { computed, inject, reactive, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import { X } from 'lucide-vue-next';

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
            !form.students.some(
                (student) => student.value === option.value,
            ),
    );
});

watch(
    () => addStudentsModal.isOpen.value,
    async (isOpen: boolean) => {
        if (!isOpen || !firstSlot.value) return;

        resetForm();

        await Promise.all([
            loadStudents(),
            loadSlotStudents(),
        ]);
    },
);

async function loadStudents() {
    if (!firstSlot.value) return;

    try {
        loadingStudents.value = true;

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
            `/schedule-enrollment/slots/${slotId}/students`
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

    const studentObj = typeof student === 'number'
            ? studentOptions.value.find(s => s.value === student)
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
            `/schedule-enrollment/slots/${firstSlot.value.id}/students/${studentId}`
        );

        form.students = form.students.filter(
            s => s.value !== studentId
        );

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
            error.response?.data?.message ??
                'Erro ao adicionar estudantes',
        );
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div
        v-if="addStudentsModal.isOpen.value"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-4xl rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900">
                    Adicionar estudantes
                </h2>

                <p class="text-sm text-gray-500">
                    Vincule estudantes aos dias selecionados
                </p>
            </div>

            <hr />

            <div class="mt-4 space-y-3 rounded-md bg-gray-50 p-4 text-sm">
                <div v-if="firstSlot">
                    <p>
                        <span class="font-semibold">Período:</span>
                        {{ firstSlot.period_label }}
                    </p>

                    <p>
                        <span class="font-semibold">Horário:</span>
                        {{ firstSlot.start_time.slice(0, 5) }}
                        às
                        {{ firstSlot.end_time.slice(0, 5) }}
                    </p>

                    <p>
                        <span class="font-semibold">Dias selecionados:</span>
                        {{ slots.length }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 py-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Buscar estudantes
                    </label>

                    <Multiselect
                        v-model="selectedStudent"
                        :options="availableStudents"
                        :searchable="true"
                        :loading="loadingStudents"
                        :can-clear="true"
                        :close-on-select="true"
                        label="label"
                        track-by="value"
                        placeholder="Buscar estudante"
                        @select="addStudent"
                    />

                    <p
                        v-if="!availableStudents.length"
                        class="mt-2 text-sm text-gray-500"
                    >
                        Nenhum estudante disponível
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
                            class="rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700"
                        >
                            {{ form.students.length }}
                        </span>
                    </div>

                    <div
                        class="min-h-50 max-h-50 space-y-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-3 items"
                    >
                        <div
                            v-for="student in form.students"
                            :key="student.value"
                            class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 shadow-sm"
                        >
                            <span class="text-sm text-gray-700">
                                {{ student.label }}
                            </span>

                            <div class="relative">
                                <button
                                    type="button"
                                    class="text-red-500 hover:text-red-700 cursor-pointer"
                                    @click="confirmRemoveId = student.value"
                                >
                                    <X />
                                </button>

                                <div
                                    v-if="confirmRemoveId === student.value"
                                    class="absolute right-0 top-6 z-50 w-64 rounded-md border bg-white p-3 shadow-lg"
                                >
                                    <p class="text-xs text-gray-700 mb-2">
                                        Isso vai remover o aluno e cancelar todos os agendamentos dele neste horário.
                                    </p>

                                    <div class="flex justify-end gap-2">
                                        <button
                                            class="text-xs text-gray-500 cursor-pointer"
                                            @click="confirmRemoveId = null"
                                        >
                                            Cancelar
                                        </button>

                                        <button
                                            class="text-xs text-red-600 font-bold cursor-pointer"
                                            @click="removeStudent(student.value)"
                                        >
                                            Confirmar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="loadingSlotStudents" class="flex h-40 items-center justify-center text-sm text-gray-500">
                            Carregando estudantes...
                        </div>

                        <div
                            v-else-if="!form.students.length"
                            class="flex h-40 items-center justify-center text-sm text-gray-500"
                        >
                            Nenhum estudante inscrito
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <CancelButton @click="close" />

                <SaveButton
                    :loading="loading"
                    @click="submit"
                >
                    Adicionar estudantes
                </SaveButton>
            </div>
        </div>
    </div>
</template>