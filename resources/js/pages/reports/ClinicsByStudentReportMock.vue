<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

type AppointmentStatus =
    | 'Agendado'
    | 'Confirmado'
    | 'Encaixe'
    | 'Atendido'
    | 'Remarcado(Paciente)'
    | 'Remarcado(Aluno)'
    | 'Não Compareceu'
    | 'Atendido/Atrasado'
    | 'Agendado Incorreto'
    | 'Cancelou';

type StatusGroup = 'all' | 'open' | 'done' | 'rescheduled' | 'loss_error';
type ReportTab = 'overview' | 'studentClinic' | 'inconsistencies';

interface AppointmentRow {
    id: number;
    patient: string;
    student: string;
    clinic: string;
    periodLabel: string;
    responsible: string;
    date: string;
    time: string;
    status: AppointmentStatus;
    createdAt: string;
}

const rows = ref<AppointmentRow[]>([
    {
        id: 1,
        patient: 'Maria Silva',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-01',
        time: '08:00',
        status: 'Atendido',
        createdAt: '2026-02-20',
    },
    {
        id: 2,
        patient: 'João Pereira',
        student: 'Ana Souza',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Dias',
        date: '2026-03-03',
        time: '10:00',
        status: 'Atendido/Atrasado',
        createdAt: '2026-02-20',
    },
    {
        id: 3,
        patient: 'Carla Mendes',
        student: 'Ana Souza',
        clinic: 'Clínica C',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Melo',
        date: '2026-03-05',
        time: '14:00',
        status: 'Não Compareceu',
        createdAt: '2026-02-21',
    },
    {
        id: 4,
        patient: 'Eduardo Santos',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-08',
        time: '09:00',
        status: 'Cancelou',
        createdAt: '2026-02-22',
    },
    {
        id: 5,
        patient: 'Fernanda Lima',
        student: 'Ana Souza',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-10',
        time: '11:00',
        status: 'Confirmado',
        createdAt: '2026-03-25',
    },
    {
        id: 6,
        patient: 'Gustavo Rocha',
        student: 'Ana Souza',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-12',
        time: '15:00',
        status: 'Encaixe',
        createdAt: '2026-03-26',
    },
    {
        id: 7,
        patient: 'Helena Prado',
        student: 'Bruno Lima',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-02',
        time: '08:30',
        status: 'Atendido',
        createdAt: '2026-02-19',
    },
    {
        id: 8,
        patient: 'Igor Nunes',
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-03-04',
        time: '13:00',
        status: 'Remarcado(Paciente)',
        createdAt: '2026-02-23',
    },
    {
        id: 9,
        patient: 'Julia Freitas',
        student: 'Bruno Lima',
        clinic: 'Clínica C',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-03-06',
        time: '16:00',
        status: 'Remarcado(Aluno)',
        createdAt: '2026-02-23',
    },
    {
        id: 10,
        patient: 'Kleber Dias',
        student: 'Bruno Lima',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-07',
        time: '10:00',
        status: 'Agendado Incorreto',
        createdAt: '2026-03-01',
    },
    {
        id: 11,
        patient: 'Larissa Mota',
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-11',
        time: '09:00',
        status: 'Agendado',
        createdAt: '2026-03-27',
    },
    {
        id: 12,
        patient: 'Marcos Vieira',
        student: 'Bruno Lima',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-13',
        time: '17:00',
        status: 'Confirmado',
        createdAt: '2026-03-28',
    },
    {
        id: 13,
        patient: 'Nina Cardoso',
        student: 'Diego Alves',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-01',
        time: '11:00',
        status: 'Atendido',
        createdAt: '2026-02-18',
    },
    {
        id: 14,
        patient: 'Otávio Reis',
        student: 'Diego Alves',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-03-03',
        time: '15:00',
        status: 'Atendido/Atrasado',
        createdAt: '2026-02-19',
    },
    {
        id: 15,
        patient: 'Paula Teixeira',
        student: 'Diego Alves',
        clinic: 'Clínica C',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-03-05',
        time: '08:00',
        status: 'Não Compareceu',
        createdAt: '2026-02-20',
    },
    {
        id: 16,
        patient: 'Rafael Duarte',
        student: 'Diego Alves',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-08',
        time: '13:30',
        status: 'Cancelou',
        createdAt: '2026-02-21',
    },
    {
        id: 17,
        patient: 'Sabrina Melo',
        student: 'Diego Alves',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-10',
        time: '10:30',
        status: 'Encaixe',
        createdAt: '2026-03-25',
    },
    {
        id: 18,
        patient: 'Tiago Bento',
        student: 'Diego Alves',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-12',
        time: '14:30',
        status: 'Agendado',
        createdAt: '2026-03-26',
    },
    {
        id: 19,
        patient: 'Ursula Nogueira',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-14',
        time: '08:00',
        status: 'Remarcado(Paciente)',
        createdAt: '2026-04-01',
    },
    {
        id: 20,
        patient: 'Vinicius Lima',
        student: 'Ana Souza',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-15',
        time: '09:00',
        status: 'Remarcado(Aluno)',
        createdAt: '2026-04-01',
    },
    {
        id: 21,
        patient: 'Wanda Prado',
        student: 'Ana Souza',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-16',
        time: '10:00',
        status: 'Agendado Incorreto',
        createdAt: '2026-04-02',
    },
    {
        id: 22,
        patient: 'Xavier Costa',
        student: 'Bruno Lima',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-17',
        time: '11:00',
        status: 'Atendido',
        createdAt: '2026-04-03',
    },
    {
        id: 23,
        patient: 'Yasmin Duarte',
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-18',
        time: '13:00',
        status: 'Atendido/Atrasado',
        createdAt: '2026-04-03',
    },
    {
        id: 24,
        patient: 'Zeca Barbosa',
        student: 'Bruno Lima',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-19',
        time: '15:00',
        status: 'Confirmado',
        createdAt: '2026-04-04',
    },
    {
        id: 25,
        patient: 'Alice Fonseca',
        student: 'Diego Alves',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-20',
        time: '16:00',
        status: 'Agendado',
        createdAt: '2026-04-05',
    },
    {
        id: 26,
        patient: 'Breno Simões',
        student: 'Diego Alves',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-21',
        time: '08:30',
        status: 'Não Compareceu',
        createdAt: '2026-04-05',
    },
    {
        id: 27,
        patient: 'Cecília Prado',
        student: 'Diego Alves',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-22',
        time: '09:30',
        status: 'Cancelou',
        createdAt: '2026-04-06',
    },
    {
        id: 28,
        patient: 'Daniel Azevedo',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-23',
        time: '10:30',
        status: 'Encaixe',
        createdAt: '2026-04-07',
    },
    {
        id: 29,
        patient: 'Elisa Morais',
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-24',
        time: '14:00',
        status: 'Remarcado(Paciente)',
        createdAt: '2026-04-07',
    },
    {
        id: 30,
        patient: 'Felipe Tavares',
        student: 'Diego Alves',
        clinic: 'Clínica C',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Melo',
        date: '2026-04-24',
        time: '17:00',
        status: 'Remarcado(Aluno)',
        createdAt: '2026-04-08',
    },
    {
        id: 31,
        patient: 'Gabriela Matos',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-25',
        time: '11:30',
        status: 'Agendado Incorreto',
        createdAt: '2026-04-08',
    },
    {
        id: 32,
        patient: 'Henrique Souza',
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-26',
        time: '12:30',
        status: 'Confirmado',
        createdAt: '2026-04-09',
    },
]);

const selectedTab = ref<ReportTab>('overview');
const showAdvancedFilters = ref(false);
const statusGroup = ref<StatusGroup>('all');
const filterSearch = ref('');
const filterClinic = ref('all');
const filterPeriod = ref('all');
const filterDateFrom = ref('');
const filterDateTo = ref('');
const filterResponsible = ref('all');
const isExporting = ref(false);

const clinicOptions = computed(() =>
    Array.from(new Set(rows.value.map((r) => r.clinic))).sort(),
);
const periodOptions = computed(() =>
    Array.from(new Set(rows.value.map((r) => r.periodLabel))).sort(),
);
const responsibleOptions = computed(() =>
    Array.from(new Set(rows.value.map((r) => r.responsible))).sort(),
);

function groupMatches(status: AppointmentStatus) {
    if (statusGroup.value === 'all') return true;
    if (statusGroup.value === 'open')
        return ['Agendado', 'Confirmado', 'Encaixe'].includes(status);
    if (statusGroup.value === 'done')
        return ['Atendido', 'Atendido/Atrasado'].includes(status);
    if (statusGroup.value === 'rescheduled')
        return ['Remarcado(Paciente)', 'Remarcado(Aluno)'].includes(status);
    return ['Não Compareceu', 'Cancelou', 'Agendado Incorreto'].includes(
        status,
    );
}

const filteredRows = computed(() => {
    const term = filterSearch.value.trim().toLowerCase();
    return rows.value.filter((row) => {
        const bySearch =
            !term ||
            row.patient.toLowerCase().includes(term) ||
            row.student.toLowerCase().includes(term);
        const byClinic =
            filterClinic.value === 'all' || row.clinic === filterClinic.value;
        const byPeriod =
            filterPeriod.value === 'all' ||
            row.periodLabel === filterPeriod.value;
        const byDateFrom =
            !filterDateFrom.value || row.date >= filterDateFrom.value;
        const byDateTo = !filterDateTo.value || row.date <= filterDateTo.value;
        const byResponsible =
            filterResponsible.value === 'all' ||
            row.responsible === filterResponsible.value;
        return (
            bySearch &&
            byClinic &&
            byPeriod &&
            byDateFrom &&
            byDateTo &&
            byResponsible &&
            groupMatches(row.status)
        );
    });
});

function countByStatus(data: AppointmentRow[], statuses: AppointmentStatus[]) {
    return data.filter((r) => statuses.includes(r.status)).length;
}

const kpis = computed(() => {
    const data = filteredRows.value;
    const students = new Set(data.map((r) => r.student));
    const clinics = new Set(data.map((r) => r.clinic));
    const clinicsByStudent = new Map<string, Set<string>>();
    for (const row of data) {
        if (!clinicsByStudent.has(row.student))
            clinicsByStudent.set(row.student, new Set());
        clinicsByStudent.get(row.student)?.add(row.clinic);
    }
    const avgClinicsPerStudent =
        students.size === 0
            ? 0
            : Number(
                  (
                      Array.from(clinicsByStudent.values()).reduce(
                          (s, set) => s + set.size,
                          0,
                      ) / students.size
                  ).toFixed(2),
              );
    const realized = countByStatus(data, ['Atendido', 'Atendido/Atrasado']);
    const noShow = countByStatus(data, ['Não Compareceu']);
    const canceled = countByStatus(data, ['Cancelou']);
    const den = realized + noShow + canceled;
    const attendanceRate =
        den === 0 ? 0 : Number(((realized / den) * 100).toFixed(1));
    return {
        totalStudents: students.size,
        totalAppointments: data.length,
        activeClinics: clinics.size,
        avgClinicsPerStudent,
        attendanceRate,
    };
});

const studentClinicRows = computed(() => {
    const map = new Map<
        string,
        {
            student: string;
            clinic: string;
            total: number;
            realized: number;
            noShow: number;
            canceled: number;
            attendanceRate: number;
        }
    >();
    for (const row of filteredRows.value) {
        const key = `${row.student}|||${row.clinic}`;
        if (!map.has(key))
            map.set(key, {
                student: row.student,
                clinic: row.clinic,
                total: 0,
                realized: 0,
                noShow: 0,
                canceled: 0,
                attendanceRate: 0,
            });
        const agg = map.get(key)!;
        agg.total += 1;
        if (['Atendido', 'Atendido/Atrasado'].includes(row.status))
            agg.realized += 1;
        if (row.status === 'Não Compareceu') agg.noShow += 1;
        if (row.status === 'Cancelou') agg.canceled += 1;
    }
    return Array.from(map.values()).map((x) => {
        const den = x.realized + x.noShow + x.canceled;
        return {
            ...x,
            attendanceRate:
                den === 0 ? 0 : Number(((x.realized / den) * 100).toFixed(1)),
        };
    });
});

const inconsistencyRows = computed(() =>
    filteredRows.value.filter((r) => r.status === 'Agendado Incorreto'),
);

function clearFilters() {
    filterSearch.value = '';
    filterClinic.value = 'all';
    filterPeriod.value = 'all';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    filterResponsible.value = 'all';
    statusGroup.value = 'all';
}

function escapeCsv(value: string | number) {
    const text = String(value);
    return /[",\n\r]/.test(text) ? `"${text.replace(/"/g, '""')}"` : text;
}

function exportCsv(
    filename: string,
    headers: string[],
    data: Array<Array<string | number>>,
) {
    const lines = [
        headers.map(escapeCsv).join(','),
        ...data.map((row) => row.map(escapeCsv).join(',')),
    ];
    const blob = new Blob([lines.join('\n')], {
        type: 'text/csv;charset=utf-8;',
    });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

function exportCurrentTab() {
    isExporting.value = true;
    const stamp = new Date().toISOString().slice(0, 10);
    if (selectedTab.value === 'overview') {
        exportCsv(
            `clinicas-por-aluno-visao-geral-${stamp}.csv`,
            [
                'Paciente',
                'Aluno',
                'Clínica',
                'Período',
                'Data',
                'Hora',
                'Status',
            ],
            filteredRows.value.map((r) => [
                r.patient,
                r.student,
                r.clinic,
                r.periodLabel,
                r.date,
                r.time,
                r.status,
            ]),
        );
    } else if (selectedTab.value === 'studentClinic') {
        exportCsv(
            `clinicas-por-aluno-matriz-${stamp}.csv`,
            [
                'Aluno',
                'Clínica',
                'Total',
                'Realizados',
                'Não compareceu',
                'Cancelou',
                'Taxa (%)',
            ],
            studentClinicRows.value.map((r) => [
                r.student,
                r.clinic,
                r.total,
                r.realized,
                r.noShow,
                r.canceled,
                r.attendanceRate,
            ]),
        );
    } else {
        exportCsv(
            `clinicas-por-aluno-inconsistencias-${stamp}.csv`,
            [
                'Paciente',
                'Aluno',
                'Clínica',
                'Período',
                'Responsável',
                'Data',
                'Hora',
                'Criado em',
            ],
            inconsistencyRows.value.map((r) => [
                r.patient,
                r.student,
                r.clinic,
                r.periodLabel,
                r.responsible,
                r.date,
                r.time,
                r.createdAt,
            ]),
        );
    }
    isExporting.value = false;
}

function statusClass(status: AppointmentStatus) {
    if (['Atendido', 'Atendido/Atrasado'].includes(status))
        return 'bg-emerald-100 text-emerald-700';
    if (['Agendado', 'Confirmado', 'Encaixe'].includes(status))
        return 'bg-blue-100 text-blue-700';
    if (['Remarcado(Paciente)', 'Remarcado(Aluno)'].includes(status))
        return 'bg-amber-100 text-amber-700';
    if (status === 'Agendado Incorreto') return 'bg-yellow-100 text-yellow-800';
    return 'bg-rose-100 text-rose-700';
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <section class="space-y-4 rounded-lg border bg-white p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">
                            Relatórios de clínicas por Alunos
                        </h1>
                        <p class="text-sm text-gray-600"></p>
                    </div>
                    <div class="flex gap-2">
                        <select
                            v-model="selectedTab"
                            class="rounded border border-gray-300 px-3 py-2 text-sm"
                        >
                            <option value="overview">Visão geral</option>
                            <option value="studentClinic">
                                Aluno x Clínica
                            </option>
                            <option value="inconsistencies">
                                Inconsistências
                            </option>
                        </select>
                        <button
                            class="rounded bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-700 disabled:opacity-50"
                            :disabled="isExporting"
                            @click="exportCurrentTab"
                        >
                            {{ isExporting ? 'Exportando...' : 'Exportar CSV' }}
                        </button>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border bg-white p-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <input
                        v-model="filterSearch"
                        type="text"
                        placeholder="Buscar paciente ou aluno"
                        class="rounded border border-gray-300 px-3 py-2 text-sm"
                    />
                    <select
                        v-model="filterPeriod"
                        class="rounded border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="all">Todos os períodos</option>
                        <option v-for="p in periodOptions" :key="p" :value="p">
                            {{ p }}
                        </option>
                    </select>
                    <select
                        v-model="filterClinic"
                        class="rounded border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="all">Todas as clínicas</option>
                        <option v-for="c in clinicOptions" :key="c" :value="c">
                            {{ c }}
                        </option>
                    </select>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        class="rounded-full border px-3 py-1 text-xs"
                        :class="
                            statusGroup === 'all'
                                ? 'border-sky-600 bg-sky-50 text-sky-700'
                                : 'border-gray-200'
                        "
                        @click="statusGroup = 'all'"
                    >
                        Todos
                    </button>
                    <button
                        class="rounded-full border px-3 py-1 text-xs"
                        :class="
                            statusGroup === 'open'
                                ? 'border-sky-600 bg-sky-50 text-sky-700'
                                : 'border-gray-200'
                        "
                        @click="statusGroup = 'open'"
                    >
                        Em aberto
                    </button>
                    <button
                        class="rounded-full border px-3 py-1 text-xs"
                        :class="
                            statusGroup === 'done'
                                ? 'border-sky-600 bg-sky-50 text-sky-700'
                                : 'border-gray-200'
                        "
                        @click="statusGroup = 'done'"
                    >
                        Concluído
                    </button>
                    <button
                        class="rounded-full border px-3 py-1 text-xs"
                        :class="
                            statusGroup === 'rescheduled'
                                ? 'border-sky-600 bg-sky-50 text-sky-700'
                                : 'border-gray-200'
                        "
                        @click="statusGroup = 'rescheduled'"
                    >
                        Remarcado
                    </button>
                    <button
                        class="rounded-full border px-3 py-1 text-xs"
                        :class="
                            statusGroup === 'loss_error'
                                ? 'border-sky-600 bg-sky-50 text-sky-700'
                                : 'border-gray-200'
                        "
                        @click="statusGroup = 'loss_error'"
                    >
                        Perda/Erro
                    </button>
                    <button
                        class="ml-auto rounded border border-gray-300 px-3 py-1 text-xs hover:bg-gray-50"
                        @click="showAdvancedFilters = !showAdvancedFilters"
                    >
                        {{
                            showAdvancedFilters
                                ? 'Ocultar filtros avançados'
                                : 'Mostrar filtros avançados'
                        }}
                    </button>
                </div>

                <div
                    v-if="showAdvancedFilters"
                    class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4"
                >
                    <input
                        v-model="filterDateFrom"
                        type="date"
                        class="rounded border border-gray-300 px-3 py-2 text-sm"
                    />
                    <input
                        v-model="filterDateTo"
                        type="date"
                        class="rounded border border-gray-300 px-3 py-2 text-sm"
                    />
                    <select
                        v-model="filterResponsible"
                        class="rounded border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="all">Todos responsáveis</option>
                        <option
                            v-for="r in responsibleOptions"
                            :key="r"
                            :value="r"
                        >
                            {{ r }}
                        </option>
                    </select>
                    <button
                        class="rounded border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50"
                        @click="clearFilters"
                    >
                        Limpar filtros
                    </button>
                </div>
            </section>

            <section
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div class="rounded border bg-white p-4">
                    <p class="text-sm text-gray-500">Alunos no filtro</p>
                    <p class="text-2xl font-bold">{{ kpis.totalStudents }}</p>
                </div>
                <div class="rounded border bg-white p-4">
                    <p class="text-sm text-gray-500">Agendamentos</p>
                    <p class="text-2xl font-bold">
                        {{ kpis.totalAppointments }}
                    </p>
                </div>
                <div class="rounded border bg-white p-4">
                    <p class="text-sm text-gray-500">Clínicas ativas</p>
                    <p class="text-2xl font-bold">{{ kpis.activeClinics }}</p>
                </div>
                <div class="rounded border bg-white p-4">
                    <p class="text-sm text-gray-500">Taxa comparecimento</p>
                    <p class="text-2xl font-bold text-emerald-700">
                        {{ kpis.attendanceRate }}%
                    </p>
                    <p class="text-xs text-gray-500">
                        Média clínicas/aluno: {{ kpis.avgClinicsPerStudent }}
                    </p>
                </div>
            </section>

            <section
                v-if="selectedTab === 'overview'"
                class="overflow-auto rounded border bg-white"
            >
                <div
                    v-if="!filteredRows.length"
                    class="p-6 text-sm text-gray-500"
                >
                    Nenhum registro encontrado para os filtros aplicados.
                </div>
                <table v-else class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="px-3 py-2">Paciente</th>
                            <th class="px-3 py-2">Aluno</th>
                            <th class="px-3 py-2">Clínica</th>
                            <th class="px-3 py-2">Período</th>
                            <th class="px-3 py-2">Data</th>
                            <th class="px-3 py-2">Hora</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in filteredRows"
                            :key="row.id"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ row.patient }}</td>
                            <td class="px-3 py-2">{{ row.student }}</td>
                            <td class="px-3 py-2">{{ row.clinic }}</td>
                            <td class="px-3 py-2">{{ row.periodLabel }}</td>
                            <td class="px-3 py-2">{{ row.date }}</td>
                            <td class="px-3 py-2">{{ row.time }}</td>
                            <td class="px-3 py-2">
                                <span
                                    :class="statusClass(row.status)"
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    >{{ row.status }}</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section
                v-else-if="selectedTab === 'studentClinic'"
                class="overflow-auto rounded border bg-white"
            >
                <div
                    v-if="!studentClinicRows.length"
                    class="p-6 text-sm text-gray-500"
                >
                    Nenhuma agregação encontrada para os filtros aplicados.
                </div>
                <table v-else class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="px-3 py-2">Aluno</th>
                            <th class="px-3 py-2">Clínica</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Realizados</th>
                            <th class="px-3 py-2">Não compareceu</th>
                            <th class="px-3 py-2">Cancelou</th>
                            <th class="px-3 py-2">Taxa (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in studentClinicRows"
                            :key="`${row.student}-${row.clinic}`"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ row.student }}</td>
                            <td class="px-3 py-2">{{ row.clinic }}</td>
                            <td class="px-3 py-2">{{ row.total }}</td>
                            <td class="px-3 py-2">{{ row.realized }}</td>
                            <td class="px-3 py-2">{{ row.noShow }}</td>
                            <td class="px-3 py-2">{{ row.canceled }}</td>
                            <td class="px-3 py-2">{{ row.attendanceRate }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-else class="overflow-auto rounded border bg-white">
                <div
                    v-if="!inconsistencyRows.length"
                    class="p-6 text-sm text-gray-500"
                >
                    Nenhum registro com “Agendado Incorreto”.
                </div>
                <table v-else class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="px-3 py-2">Paciente</th>
                            <th class="px-3 py-2">Aluno</th>
                            <th class="px-3 py-2">Clínica</th>
                            <th class="px-3 py-2">Período</th>
                            <th class="px-3 py-2">Responsável</th>
                            <th class="px-3 py-2">Data</th>
                            <th class="px-3 py-2">Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in inconsistencyRows"
                            :key="row.id"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ row.patient }}</td>
                            <td class="px-3 py-2">{{ row.student }}</td>
                            <td class="px-3 py-2">{{ row.clinic }}</td>
                            <td class="px-3 py-2">{{ row.periodLabel }}</td>
                            <td class="px-3 py-2">{{ row.responsible }}</td>
                            <td class="px-3 py-2">{{ row.date }}</td>
                            <td class="px-3 py-2">{{ row.time }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </AppLayout>
</template>
