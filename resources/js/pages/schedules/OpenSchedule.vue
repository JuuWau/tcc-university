<script setup lang="ts">
import AppMultiselect from '@/components/AppMultiselect.vue';
import BaseInput from '@/components/inputs/BaseInput.vue';
import { Button } from '@/components/ui/button';
import { OpenScheduleKey } from '@/keys/schedules/openScheduleKeys';
import AppLayout from '@/layouts/AppLayout.vue';
import { openScheduleSchema } from '@/schemas/openSchedule.schema';
import { formatDateBr } from '@/src/utils/formatters';
import type {
    OpenScheduleErrorResponse,
    OpenScheduleOption,
    OpenSchedulePayload,
    OpenScheduleResponse,
    OpenScheduleSlot,
} from '@/types/schedule/openSchedule';
import { Switch } from '@headlessui/vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { CalendarPlus, X } from 'lucide-vue-next';
import { computed, provide, reactive, ref } from 'vue';
import { toast } from 'vue3-toastify';

type SelectOption = { label: string; value: number };
type CalendarDay = {
    key: string;
    isFiller: boolean;
    label?: number;
    dateKey?: string;
    isPast?: boolean;
    isSelected?: boolean;
    weekDay?: number;
};

const now = new Date();
const initialYear = now.getFullYear();
const initialMonthIndex = now.getMonth();
const displayYear = ref(initialYear);
const displayMonthIndex = ref(initialMonthIndex);
const todayDateString = toDateKey(now);
const weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

const page = usePage();
const props = page.props as {
    periods?: OpenScheduleOption[];
    responsible?: OpenScheduleOption[];
    clinics?: OpenScheduleOption[];
    existingSlots?: OpenScheduleSlot[];
};

const periodOptions: SelectOption[] = (props.periods ?? []).map((period) => ({
    label: period.label,
    value: period.id,
}));

const responsibleOptions: SelectOption[] = (props.responsible ?? []).map(
    (responsible: OpenScheduleOption) => ({
        label: responsible.label,
        value: responsible.id,
    }),
);
const clinicOptions: SelectOption[] = (props.clinics ?? []).map((clinic) => ({
    label: clinic.label,
    value: clinic.id,
}));

const existingSlots = ref<OpenScheduleSlot[]>(props.existingSlots ?? []);
const loading = ref(false);
provide(OpenScheduleKey, { loading });

const form = reactive({
    clinic_id: null as number | null,
    available_slots: '' as string | number,
    period_id: null as number | null,
    responsible_ids: [] as number[],
    days: [] as string[],
    start_time: '',
    end_time: '',
    allow_student_booking: false,
    allow_student_enrollment: false,
    allow_procedure_booking: false,
});

const monthLabel = computed(() =>
    new Date(displayYear.value, displayMonthIndex.value, 1).toLocaleDateString(
        'pt-BR',
        {
            month: 'long',
            year: 'numeric',
        },
    ),
);

const periodLabel = computed(
    () =>
        periodOptions.find((option) => option.value === form.period_id)
            ?.label ?? '—',
);
const responsibleLabel = computed(
    () =>
        responsibleOptions
            .filter((option) => form.responsible_ids.includes(option.value))
            .map((option) => option.label)
            .join(', ') || '—',
);
const sortedDays = computed(() =>
    [...form.days].sort((a, b) => a.localeCompare(b)),
);

const normalizedAvailableChairs = computed(() => {
    if (form.available_slots === '' || form.available_slots === null)
        return null;
    const parsed = Number(form.available_slots);
    return Number.isNaN(parsed) ? null : parsed;
});

const calendarDays = computed(() => {
    const daysInMonth = new Date(
        displayYear.value,
        displayMonthIndex.value + 1,
        0,
    ).getDate();
    const startWeekDay = new Date(
        displayYear.value,
        displayMonthIndex.value,
        1,
    ).getDay();

    const fillers: CalendarDay[] = Array.from(
        { length: startWeekDay },
        (_, index) => ({
            key: `filler-${index}`,
            isFiller: true,
        }),
    );

    const days: CalendarDay[] = Array.from(
        { length: daysInMonth },
        (_, index) => {
            const day = index + 1;
            const date = new Date(
                displayYear.value,
                displayMonthIndex.value,
                day,
            );
            const dateKey = toDateKey(date);
            return {
                key: dateKey,
                label: day,
                dateKey,
                isPast: dateKey < todayDateString,
                isSelected: form.days.includes(dateKey),
                isFiller: false,
                weekDay: date.getDay(),
            };
        },
    );

    return [...fillers, ...days];
});

const currentMonthSelectableDays = computed(() =>
    calendarDays.value.filter(
        (day) => !day.isFiller && !day.isPast && day.dateKey,
    ),
);

const formValidationResult = computed(() =>
    openScheduleSchema.safeParse({
        clinic_id: form.clinic_id,
        available_slots: normalizedAvailableChairs.value,
        period_id: form.period_id,
        responsible_ids: form.responsible_ids,
        days: form.days,
        start_time: form.start_time,
        end_time: form.end_time,
        allow_student_booking: form.allow_student_booking,
        allow_student_enrollment: form.allow_student_enrollment,
        allow_procedure_booking: form.allow_procedure_booking,
    }),
);

const clinicLabel = computed(
    () =>
        clinicOptions.find((option) => option.value === form.clinic_id)
            ?.label ?? '—',
);

const conflictPreview = computed(() => {
    if (
        !form.clinic_id ||
        !form.start_time ||
        !form.end_time ||
        form.end_time <= form.start_time
    ) {
        return null;
    }

    return (
        findConflicts({
            clinic_id: form.clinic_id,
            days: form.days,
            start_time: form.start_time,
            end_time: form.end_time,
        })[0] ?? null
    );
});

const isFormReady = computed(
    () => formValidationResult.value.success && !conflictPreview.value,
);

function toDateKey(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function goToPreviousMonth() {
    if (displayMonthIndex.value === 0) {
        displayMonthIndex.value = 11;
        displayYear.value -= 1;
        return;
    }
    displayMonthIndex.value -= 1;
}

function goToNextMonth() {
    if (displayMonthIndex.value === 11) {
        displayMonthIndex.value = 0;
        displayYear.value += 1;
        return;
    }
    displayMonthIndex.value += 1;
}

function goToCurrentMonth() {
    displayYear.value = initialYear;
    displayMonthIndex.value = initialMonthIndex;
}

function toggleDay(day: CalendarDay) {
    if (day.isFiller || day.isPast || !day.dateKey) return;
    const index = form.days.indexOf(day.dateKey);
    if (index === -1) {
        form.days.push(day.dateKey);
        return;
    }
    form.days.splice(index, 1);
}

function addDaysByWeekDay(weekDay: number) {
    currentMonthSelectableDays.value.forEach((day) => {
        if (day.weekDay !== weekDay || !day.dateKey) return;
        if (!form.days.includes(day.dateKey)) form.days.push(day.dateKey);
    });
}

function selectWeekDaysCurrentMonth() {
    currentMonthSelectableDays.value.forEach((day) => {
        if (
            typeof day.weekDay !== 'number' ||
            day.weekDay === 0 ||
            day.weekDay === 6 ||
            !day.dateKey
        )
            return;
        if (!form.days.includes(day.dateKey)) form.days.push(day.dateKey);
    });
}

function clearCurrentMonthSelection() {
    const currentMonthPrefix = `${displayYear.value}-${String(displayMonthIndex.value + 1).padStart(2, '0')}`;
    form.days = form.days.filter((day) => !day.startsWith(currentMonthPrefix));
}

function clearSelection() {
    form.clinic_id = null;
    form.available_slots = '';
    form.period_id = null;
    form.responsible_ids = [];
    form.days = [];
    form.start_time = '';
    form.end_time = '';
    form.allow_student_booking = false;
    form.allow_student_enrollment = false;
    form.allow_procedure_booking = false;
}

function hasTimeOverlap(
    startA: string,
    endA: string,
    startB: string,
    endB: string,
): boolean {
    return startA < endB && endA > startB;
}

function findConflicts(input: {
    clinic_id: number;
    days: string[];
    start_time: string;
    end_time: string;
}) {
    return existingSlots.value.filter((slot) => {
        if (slot.clinic_id !== input.clinic_id) return false;
        if (!input.days.includes(String(slot.date).slice(0, 10))) return false;
        return hasTimeOverlap(
            input.start_time,
            input.end_time,
            String(slot.start_time).slice(0, 5),
            String(slot.end_time).slice(0, 5),
        );
    });
}

async function submit() {
    if (loading.value) return;

    const result = formValidationResult.value;
    if (!result.success) {
        toast.error(result.error.issues[0].message);
        return;
    }

    const conflicts = findConflicts({
        clinic_id: result.data.clinic_id,
        days: result.data.days,
        start_time: result.data.start_time,
        end_time: result.data.end_time,
    });
    if (conflicts.length) {
        const conflict = conflicts[0];
        toast.error(
            `Conflito: ${conflict.clinic_name} já tem agenda em ${conflict.date} (${conflict.start_time} às ${conflict.end_time}).`,
        );
        return;
    }

    const payload: OpenSchedulePayload = {
        clinic_id: result.data.clinic_id,
        available_slots: result.data.available_slots,
        allow_student_booking: result.data.allow_student_booking,
        allow_student_enrollment: result.data.allow_student_enrollment,
        allow_procedure_booking: result.data.allow_procedure_booking,
        period_id: result.data.period_id,
        responsible_ids: result.data.responsible_ids,
        days: [...result.data.days].sort((a, b) => a.localeCompare(b)),
        start_time: result.data.start_time,
        end_time: result.data.end_time,
    };

    try {
        loading.value = true;
        const { data } = await axios.post<OpenScheduleResponse>(
            '/schedules/open',
            payload,
        );
        existingSlots.value = [...existingSlots.value, ...(data.slots ?? [])];
        console.log('OpenSchedule payload:', payload);
        toast.success(data.message ?? 'Agenda cadastrada com sucesso');
        clearSelection();
    } catch (error: any) {
        const errData: OpenScheduleErrorResponse | undefined =
            error.response?.data;
        if (errData?.conflict) {
            toast.error(
                `${errData.message ?? 'Conflito de agenda.'} (${errData.conflict.date} - ${errData.conflict.start_time} às ${errData.conflict.end_time})`,
            );
            return;
        }
        toast.error(errData?.message ?? 'Erro ao cadastrar agenda');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto my-8 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h1
                        class="text-xl font-semibold tracking-tight text-gray-900"
                    >
                        Abrir agenda
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Selecione os dias, período, clínica e horário para abrir
                        as agendas.
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                    <section
                        class="order-1 space-y-6 lg:col-span-2 lg:row-span-2"
                    >
                        <div
                            class="grid grid-cols-1 items-end gap-4 md:grid-cols-2"
                        >
                            <AppMultiselect
                                v-model="form.clinic_id"
                                :options="clinicOptions"
                                field-label="Clínica (*)"
                                label="label"
                                value-prop="value"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                :append-to-body="true"
                                placeholder="Selecione a clínica"
                            />
                            <BaseInput
                                v-model="form.available_slots"
                                label="Cadeiras livres"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="Ex: 6"
                            />
                        </div>
                        <div class="space-y-3">
                            <div
                                class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        Permitir inscrição de alunos
                                    </p>
                                    <p
                                        class="mt-1 text-xs leading-relaxed text-gray-500"
                                    >
                                        Se desativado, a ocupação das vagas
                                        deverá ser gerenciada manualmente pela
                                        equipe da clínica.
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
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        Inscrever automaticamente os alunos do
                                        período
                                    </p>
                                    <p
                                        class="mt-1 text-xs leading-relaxed text-gray-500"
                                    >
                                        Os alunos do período selecionado serão
                                        inscritos automaticamente nos horários
                                        abertos para a clínica.
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
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        Permitir registro de procedimento
                                    </p>
                                    <p
                                        class="mt-1 text-xs leading-relaxed text-gray-500"
                                    >
                                        Permite que os alunos registrem
                                        procedimentos no agendamento do
                                        paciente.
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
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div
                                class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4"
                            >
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        class="h-8 w-8 cursor-pointer p-0"
                                        @click="goToPreviousMonth"
                                    >
                                        &lt;
                                    </Button>
                                    <h2
                                        class="min-w-44 text-center text-sm font-semibold text-gray-800 capitalize"
                                    >
                                        {{ monthLabel }}
                                    </h2>
                                    <Button
                                        variant="outline"
                                        class="h-8 w-8 cursor-pointer p-0"
                                        @click="goToNextMonth"
                                    >
                                        &gt;
                                    </Button>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Button
                                        variant="outline"
                                        class="h-8 cursor-pointer px-3 text-xs"
                                        @click="goToCurrentMonth"
                                    >
                                        Hoje
                                    </Button>
                                    <span class="text-xs text-gray-500">
                                        Selecione múltiplos dias
                                    </span>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap gap-2">
                                <Button
                                    variant="outline"
                                    class="h-8 cursor-pointer px-3 text-xs"
                                    @click="selectWeekDaysCurrentMonth"
                                >
                                    Seg-Sex
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-8 cursor-pointer px-3 text-xs"
                                    @click="addDaysByWeekDay(6)"
                                >
                                    Todos os sábados
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-8 cursor-pointer px-3 text-xs"
                                    @click="clearCurrentMonthSelection"
                                >
                                    Limpar mês
                                </Button>
                            </div>
                            <div class="mb-2 grid grid-cols-7 gap-2">
                                <div
                                    v-for="weekDay in weekDays"
                                    :key="weekDay"
                                    class="text-center text-xs font-semibold text-gray-500"
                                >
                                    {{ weekDay }}
                                </div>
                            </div>
                            <div class="grid grid-cols-7 gap-2">
                                <button
                                    v-for="day in calendarDays"
                                    :key="day.key"
                                    type="button"
                                    :disabled="day.isFiller || day.isPast"
                                    class="flex h-10 items-center justify-center rounded-lg border text-sm transition"
                                    :class="[
                                        day.isFiller
                                            ? 'border-transparent bg-transparent'
                                            : day.isPast
                                              ? 'cursor-not-allowed border-gray-100 bg-gray-100 text-gray-400'
                                              : day.isSelected
                                                ? 'cursor-pointer border-sky-600 bg-sky-600 font-semibold text-white shadow-sm'
                                                : 'cursor-pointer border-gray-200 bg-white text-gray-700 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700',
                                    ]"
                                    @click="toggleDay(day)"
                                >
                                    <span v-if="!day.isFiller">
                                        {{ day.label }}
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <AppMultiselect
                                v-model="form.period_id"
                                :options="periodOptions"
                                field-label="Período (*)"
                                label="label"
                                value-prop="value"
                                :searchable="true"
                                :close-on-select="true"
                                :can-clear="true"
                                :append-to-body="true"
                                placeholder="Selecione o período"
                            />
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
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <BaseInput
                                v-model="form.start_time"
                                label="Horário de início (*)"
                                type="text"
                                v-mask="'##:##'"
                                placeholder="HH:mm"
                            />
                            <BaseInput
                                v-model="form.end_time"
                                label="Horário de fim (*)"
                                type="text"
                                v-mask="'##:##'"
                                placeholder="HH:mm"
                            />
                        </div>
                        <div
                            v-if="conflictPreview"
                            class="rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800"
                        >
                            Conflito detectado: a clínica
                            <strong>{{ conflictPreview.clinic_name }}</strong>
                            já possui agenda aberta no dia
                            <strong>
                                {{ formatDateBr(conflictPreview.date) }}
                            </strong>
                            entre
                            <strong>
                                {{ conflictPreview.start_time }} às
                                {{ conflictPreview.end_time }} </strong
                            >.
                        </div>
                    </section>
                    <aside
                        class="order-2 h-fit rounded-xl border border-gray-200 bg-gray-50 p-5 lg:col-start-3 lg:row-start-1"
                    >
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-base font-semibold text-gray-900">
                                Resumo da abertura
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Confira os dados antes de cadastrar.
                            </p>
                        </div>
                        <div class="space-y-3 py-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-500">Clínica</p>
                                <p class="font-medium text-gray-800">
                                    {{ clinicLabel }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">
                                    Cadeiras livres
                                </p>
                                <p class="font-medium text-gray-800">
                                    {{
                                        normalizedAvailableChairs !== null
                                            ? normalizedAvailableChairs
                                            : '—'
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">
                                    Dias selecionados
                                </p>
                                <p class="font-medium text-gray-800">
                                    {{ sortedDays.length }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Período</p>
                                <p class="font-medium text-gray-800">
                                    {{ periodLabel }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">
                                    Responsáveis
                                </p>
                                <p class="font-medium text-gray-800">
                                    {{ responsibleLabel }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Horário</p>
                                <p class="font-medium text-gray-800">
                                    {{
                                        form.start_time && form.end_time
                                            ? `${form.start_time} às ${form.end_time}`
                                            : '—'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-4">
                            <p class="mb-2 text-sm font-medium text-gray-800">
                                Dias escolhidos
                            </p>
                            <p
                                v-if="!sortedDays.length"
                                class="text-sm text-gray-500 italic"
                            >
                                Nenhum dia selecionado ainda.
                            </p>
                            <ul
                                v-else
                                class="max-h-56 space-y-1 overflow-y-auto"
                            >
                                <li
                                    v-for="day in sortedDays"
                                    :key="day"
                                    class="rounded-md border border-gray-100 bg-white px-3 py-2 text-sm text-gray-700"
                                >
                                    {{ formatDateBr(day) }}
                                </li>
                            </ul>
                        </div>
                    </aside>
                    <div
                        class="order-3 flex flex-wrap justify-end gap-2 lg:col-span-2 lg:col-start-1 lg:row-start-3"
                    >
                        <Button
                            variant="outline"
                            class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-lg border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 active:scale-[0.98]"
                            @click="clearSelection"
                        >
                            <X class="h-4 w-4" /> Limpar seleção
                        </Button>
                        <Button
                            class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 focus:outline-none active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="!isFormReady || loading"
                            @click="submit"
                        >
                            <LoadingSpinner v-if="loading" class="h-4 w-4" />
                            <CalendarPlus v-else class="h-4 w-4" />
                            <span>
                                {{
                                    loading ? 'Salvando...' : 'Cadastrar agenda'
                                }}
                            </span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
