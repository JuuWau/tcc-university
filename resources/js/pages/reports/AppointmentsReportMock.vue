<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

const showAdvancedFilters = ref(false);
/** Status alinhados a PatientSchedules.vue */
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

const STATUS_LIST: AppointmentStatus[] = [
    'Agendado',
    'Confirmado',
    'Encaixe',
    'Atendido',
    'Remarcado(Paciente)',
    'Remarcado(Aluno)',
    'Não Compareceu',
    'Atendido/Atrasado',
    'Agendado Incorreto',
    'Cancelou',
];

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
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-03-02',
        time: '09:00',
        status: 'Não Compareceu',
        createdAt: '2026-02-21',
    },
    {
        id: 3,
        patient: 'Carla Mendes',
        student: 'Diego Alves',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-03',
        time: '10:00',
        status: 'Cancelou',
        createdAt: '2026-02-22',
    },
    {
        id: 4,
        patient: 'Eduardo Santos',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-04',
        time: '14:00',
        status: 'Atendido/Atrasado',
        createdAt: '2026-02-23',
    },
    {
        id: 5,
        patient: 'Fernanda Lima',
        student: 'Bruno Lima',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-10',
        time: '08:30',
        status: 'Agendado',
        createdAt: '2026-03-25',
    },
    {
        id: 6,
        patient: 'Gustavo Rocha',
        student: 'Diego Alves',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-10',
        time: '10:00',
        status: 'Confirmado',
        createdAt: '2026-03-26',
    },
    {
        id: 7,
        patient: 'Helena Prado',
        student: 'Ana Souza',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-11',
        time: '09:00',
        status: 'Encaixe',
        createdAt: '2026-04-05',
    },
    {
        id: 8,
        patient: 'Igor Nunes',
        student: 'Bruno Lima',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-12',
        time: '11:00',
        status: 'Remarcado(Paciente)',
        createdAt: '2026-03-28',
    },
    {
        id: 9,
        patient: 'Julia Freitas',
        student: 'Diego Alves',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-12',
        time: '15:00',
        status: 'Remarcado(Aluno)',
        createdAt: '2026-03-29',
    },
    {
        id: 10,
        patient: 'Kleber Dias',
        student: 'Ana Souza',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-03-05',
        time: '16:00',
        status: 'Agendado Incorreto',
        createdAt: '2026-03-01',
    },
    {
        id: 11,
        patient: 'Larissa Mota',
        student: 'Bruno Lima',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-04-15',
        time: '08:00',
        status: 'Confirmado',
        createdAt: '2026-04-01',
    },
    {
        id: 12,
        patient: 'Marcos Vieira',
        student: 'Diego Alves',
        clinic: 'Clínica A',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-06',
        time: '09:00',
        status: 'Atendido',
        createdAt: '2026-02-25',
    },
    {
        id: 13,
        patient: 'Nina Cardoso',
        student: 'Ana Souza',
        clinic: 'Clínica B',
        periodLabel: '2026/2 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-04-20',
        time: '14:00',
        status: 'Agendado',
        createdAt: '2026-04-02',
    },
    {
        id: 14,
        patient: 'Otávio Reis',
        student: 'Bruno Lima',
        clinic: 'Clínica A',
        periodLabel: '2026/1 - 3º ano',
        responsible: 'Prof. Costa',
        date: '2026-03-07',
        time: '10:30',
        status: 'Agendado Incorreto',
        createdAt: '2026-03-02',
    },
    {
        id: 15,
        patient: 'Paula Teixeira',
        student: 'Diego Alves',
        clinic: 'Clínica B',
        periodLabel: '2026/1 - 2º ano',
        responsible: 'Prof. Dias',
        date: '2026-03-08',
        time: '11:00',
        status: 'Não Compareceu',
        createdAt: '2026-03-03',
    },
]);

const filterDateFrom = ref('');
const filterDateTo = ref('');
const filterClinic = ref<string>('all');
const filterPeriod = ref<string>('all');
const filterResponsible = ref<string>('all');
/** vazio = todos os status */
const filterStatuses = ref<AppointmentStatus[]>([]);
const search = ref('');

const clinicOptions = computed(() => {
    const set = new Set(rows.value.map((r) => r.clinic));
    return ['all', ...Array.from(set).sort()];
});

const periodOptions = computed(() => {
    const set = new Set(rows.value.map((r) => r.periodLabel));
    return ['all', ...Array.from(set).sort()];
});

const responsibleOptions = computed(() => {
    const set = new Set(rows.value.map((r) => r.responsible));
    return ['all', ...Array.from(set).sort()];
});

function toggleStatus(s: AppointmentStatus) {
    const i = filterStatuses.value.indexOf(s);
    if (i === -1) filterStatuses.value = [...filterStatuses.value, s];
    else filterStatuses.value = filterStatuses.value.filter((x) => x !== s);
}

function clearStatusFilter() {
    filterStatuses.value = [];
}

function rowDateTime(row: AppointmentRow): Date {
    return new Date(`${row.date}T${row.time}:00`);
}

function isPastAppointment(row: AppointmentRow): boolean {
    return rowDateTime(row) < new Date();
}

const baseFiltered = computed(() => {
    return rows.value.filter((row) => {
        if (filterDateFrom.value && row.date < filterDateFrom.value)
            return false;
        if (filterDateTo.value && row.date > filterDateTo.value) return false;
        if (filterClinic.value !== 'all' && row.clinic !== filterClinic.value)
            return false;
        if (
            filterPeriod.value !== 'all' &&
            row.periodLabel !== filterPeriod.value
        )
            return false;
        if (
            filterResponsible.value !== 'all' &&
            row.responsible !== filterResponsible.value
        )
            return false;
        if (
            filterStatuses.value.length > 0 &&
            !filterStatuses.value.includes(row.status)
        )
            return false;

        const term = search.value.trim().toLowerCase();
        if (!term) return true;
        return (
            row.patient.toLowerCase().includes(term) ||
            row.student.toLowerCase().includes(term)
        );
    });
});

/** Tabela principal: mesmo conjunto filtrado */
const filtered = baseFiltered;

function countStatus(
    list: AppointmentRow[],
    statuses: AppointmentStatus[],
): number {
    const set = new Set(statuses);
    return list.filter((r) => set.has(r.status)).length;
}

const kpis = computed(() => {
    const list = baseFiltered.value;
    const total = list.length;

    const pastForComparecimento = list.filter(
        (r) =>
            isPastAppointment(r) &&
            [
                'Atendido',
                'Atendido/Atrasado',
                'Não Compareceu',
                'Cancelou',
            ].includes(r.status),
    );
    const attPast = countStatus(pastForComparecimento, [
        'Atendido',
        'Atendido/Atrasado',
    ]);
    const ncPast = countStatus(pastForComparecimento, ['Não Compareceu']);
    const cxPast = countStatus(pastForComparecimento, ['Cancelou']);

    const denomComparecimento = attPast + ncPast + cxPast;
    const taxaComparecimento =
        denomComparecimento === 0
            ? null
            : Math.round((attPast / denomComparecimento) * 1000) / 10;

    const agendado = countStatus(list, ['Agendado']);
    const confirmado = countStatus(list, ['Confirmado']);
    const convDenom = agendado + confirmado;
    const conversaoAgendadoConfirmado =
        convDenom === 0
            ? null
            : Math.round((confirmado / convDenom) * 1000) / 10;

    const encaixe = countStatus(list, ['Encaixe']);
    const pctEncaixe =
        total === 0 ? null : Math.round((encaixe / total) * 1000) / 10;

    const remPac = countStatus(list, ['Remarcado(Paciente)']);
    const remAlu = countStatus(list, ['Remarcado(Aluno)']);

    const atendidoOnly = countStatus(list, ['Atendido']);
    const atendidoAtrasado = countStatus(list, ['Atendido/Atrasado']);
    const denomAtraso = atendidoOnly + atendidoAtrasado;
    const pctAtraso =
        denomAtraso === 0
            ? null
            : Math.round((atendidoAtrasado / denomAtraso) * 1000) / 10;

    const incorreto = countStatus(list, ['Agendado Incorreto']);

    return {
        total,
        taxaComparecimento,
        comparecimentoDetail: {
            attPast,
            ncPast,
            cxPast,
            denom: denomComparecimento,
        },
        conversaoAgendadoConfirmado,
        convDetail: { agendado, confirmado, denom: convDenom },
        pctEncaixe,
        encaixe,
        remPac,
        remAlu,
        pctAtraso,
        atendidoOnly,
        atendidoAtrasado,
        incorreto,
    };
});

const productivityByStudent = computed(() => {
    const map = new Map<
        string,
        {
            total: number;
            realizados: number;
            perdidos: number;
            encaixe: number;
            incorreto: number;
        }
    >();
    for (const r of baseFiltered.value) {
        const cur = map.get(r.student) ?? {
            total: 0,
            realizados: 0,
            perdidos: 0,
            encaixe: 0,
            incorreto: 0,
        };
        cur.total += 1;
        if (r.status === 'Atendido' || r.status === 'Atendido/Atrasado')
            cur.realizados += 1;
        if (r.status === 'Não Compareceu' || r.status === 'Cancelou')
            cur.perdidos += 1;
        if (r.status === 'Encaixe') cur.encaixe += 1;
        if (r.status === 'Agendado Incorreto') cur.incorreto += 1;
        map.set(r.student, cur);
    }
    return Array.from(map.entries())
        .map(([student, v]) => ({ student, ...v }))
        .sort((a, b) => a.student.localeCompare(b.student));
});

const clinicsAggregate = computed(() => {
    const map = new Map<
        string,
        {
            total: number;
            realizados: number;
            perdidos: number;
            encaixe: number;
            incorreto: number;
            remPac: number;
            remAlu: number;
        }
    >();
    for (const r of baseFiltered.value) {
        const cur = map.get(r.clinic) ?? {
            total: 0,
            realizados: 0,
            perdidos: 0,
            encaixe: 0,
            incorreto: 0,
            remPac: 0,
            remAlu: 0,
        };
        cur.total += 1;
        if (r.status === 'Atendido' || r.status === 'Atendido/Atrasado')
            cur.realizados += 1;
        if (r.status === 'Não Compareceu' || r.status === 'Cancelou')
            cur.perdidos += 1;
        if (r.status === 'Encaixe') cur.encaixe += 1;
        if (r.status === 'Agendado Incorreto') cur.incorreto += 1;
        if (r.status === 'Remarcado(Paciente)') cur.remPac += 1;
        if (r.status === 'Remarcado(Aluno)') cur.remAlu += 1;
        map.set(r.clinic, cur);
    }
    return Array.from(map.entries())
        .map(([clinic, v]) => ({ clinic, ...v }))
        .sort((a, b) => a.clinic.localeCompare(b.clinic));
});

const remarcacoesByClinicPeriod = computed(() => {
    const key = (r: AppointmentRow) => `${r.clinic}|||${r.periodLabel}`;
    const map = new Map<
        string,
        { clinic: string; period: string; paciente: number; aluno: number }
    >();
    for (const r of baseFiltered.value) {
        if (
            r.status !== 'Remarcado(Paciente)' &&
            r.status !== 'Remarcado(Aluno)'
        )
            continue;
        const k = key(r);
        const cur = map.get(k) ?? {
            clinic: r.clinic,
            period: r.periodLabel,
            paciente: 0,
            aluno: 0,
        };
        if (r.status === 'Remarcado(Paciente)') cur.paciente += 1;
        else cur.aluno += 1;
        map.set(k, cur);
    }
    return Array.from(map.values()).sort((a, b) =>
        `${a.clinic}${a.period}`.localeCompare(`${b.clinic}${b.period}`),
    );
});

const incorretoByWeek = computed(() => {
    const list = baseFiltered.value.filter(
        (r) => r.status === 'Agendado Incorreto',
    );
    const buckets = new Map<string, number>();
    for (const r of list) {
        const d = new Date(r.createdAt + 'T12:00:00');
        const y = d.getFullYear();
        const w = getWeekNumber(d);
        const label = `${y}-S${String(w).padStart(2, '0')}`;
        buckets.set(label, (buckets.get(label) ?? 0) + 1);
    }
    return Array.from(buckets.entries())
        .map(([week, count]) => ({ week, count }))
        .sort((a, b) => a.week.localeCompare(b.week));
});

function getWeekNumber(d: Date): number {
    const t = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const day = t.getUTCDay() || 7;
    t.setUTCDate(t.getUTCDate() + 4 - day);
    const y = new Date(Date.UTC(t.getUTCFullYear(), 0, 1));
    return Math.ceil(((+t - +y) / 86400000 + 1) / 7);
}

function statusBadgeClass(status: AppointmentStatus): string {
    switch (status) {
        case 'Agendado':
            return 'bg-blue-100 text-blue-800';
        case 'Confirmado':
            return 'bg-green-100 text-green-800';
        case 'Encaixe':
            return 'bg-purple-100 text-purple-800';
        case 'Atendido':
            return 'bg-gray-200 text-gray-800';
        case 'Remarcado(Paciente)':
            return 'bg-amber-100 text-amber-900';
        case 'Remarcado(Aluno)':
            return 'bg-orange-100 text-orange-900';
        case 'Não Compareceu':
            return 'bg-orange-100 text-orange-700';
        case 'Atendido/Atrasado':
            return 'bg-teal-100 text-teal-800';
        case 'Agendado Incorreto':
            return 'bg-yellow-100 text-yellow-900';
        case 'Cancelou':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-600';
    }
}

const selectedTab = ref<
    'overview' | 'productivity' | 'clinics' | 'remarcacoes' | 'incorretos'
>('overview');

const isExporting = ref(false);

function escapeCsvCell(value: string | number): string {
    const s = String(value);
    if (/[",\n\r]/.test(s)) return `"${s.replace(/"/g, '""')}"`;
    return s;
}

function downloadCsv(
    filename: string,
    headers: string[],
    dataRows: (string | number)[][],
) {
    const lines = [
        headers.map(escapeCsvCell).join(','),
        ...dataRows.map((row) => row.map(escapeCsvCell).join(',')),
    ];
    const blob = new Blob([lines.join('\n')], {
        type: 'text/csv;charset=utf-8;',
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function exportCurrentReport() {
    isExporting.value = true;
    const stamp = new Date().toISOString().slice(0, 10);

    try {
        if (selectedTab.value === 'overview') {
            downloadCsv(
                `relatorio-pacientes-agendados-${stamp}.csv`,
                [
                    'Paciente',
                    'Aluno',
                    'Clinica',
                    'Periodo',
                    'Responsavel',
                    'Data',
                    'Hora',
                    'Status',
                ],
                filtered.value.map((r) => [
                    r.patient,
                    r.student,
                    r.clinic,
                    r.periodLabel,
                    r.responsible,
                    r.date,
                    r.time,
                    r.status,
                ]),
            );
        } else if (selectedTab.value === 'productivity') {
            downloadCsv(
                `relatorio-produtividade-alunos-${stamp}.csv`,
                [
                    'Aluno',
                    'Total',
                    'Realizados',
                    'Perdidos',
                    'Encaixe',
                    'Agendado Incorreto',
                ],
                productivityByStudent.value.map((x) => [
                    x.student,
                    x.total,
                    x.realizados,
                    x.perdidos,
                    x.encaixe,
                    x.incorreto,
                ]),
            );
        } else if (selectedTab.value === 'clinics') {
            downloadCsv(
                `relatorio-por-clinica-${stamp}.csv`,
                [
                    'Clinica',
                    'Total',
                    'Realizados',
                    'Perdidos',
                    'Encaixe',
                    'Incorreto',
                    'Rem Paciente',
                    'Rem Aluno',
                ],
                clinicsAggregate.value.map((x) => [
                    x.clinic,
                    x.total,
                    x.realizados,
                    x.perdidos,
                    x.encaixe,
                    x.incorreto,
                    x.remPac,
                    x.remAlu,
                ]),
            );
        } else if (selectedTab.value === 'remarcacoes') {
            downloadCsv(
                `relatorio-remarcacoes-${stamp}.csv`,
                ['Clinica', 'Periodo', 'Rem Paciente', 'Rem Aluno'],
                remarcacoesByClinicPeriod.value.map((x) => [
                    x.clinic,
                    x.period,
                    x.paciente,
                    x.aluno,
                ]),
            );
        } else {
            downloadCsv(
                `relatorio-agendado-incorreto-semana-${stamp}.csv`,
                ['Semana_criacao', 'Quantidade'],
                incorretoByWeek.value.map((x) => [x.week, x.count]),
            );
        }
    } finally {
        isExporting.value = false;
    }
}

function clearFilters() {
    filterDateFrom.value = '';
    filterDateTo.value = '';
    filterClinic.value = 'all';
    filterPeriod.value = 'all';
    filterResponsible.value = 'all';
    filterStatuses.value = [];
    search.value = '';
}

function fmtPct(v: number | null): string {
    if (v === null) return '—';
    return `${v}%`;
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <section class="space-y-4 rounded-lg border bg-white p-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Relatório de pacientes agendados
                    </h1>
                    <p class="text-sm text-gray-600">

                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedTab"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="overview">Visão geral</option>
                        <option value="productivity">Por aluno</option>
                        <option value="clinics">Por clínica</option>
                        <option value="remarcacoes">Remarcações</option>
                        <option value="incorretos">
                            Agendado incorreto (semana)
                        </option>
                    </select>

                    <button
                        type="button"
                        class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-700 disabled:opacity-50"
                        :disabled="isExporting"
                        @click="exportCurrentReport"
                    >
                        {{ isExporting ? 'Exportando...' : 'Exportar CSV' }}
                    </button>

                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50"
                        @click="clearFilters"
                    >
                        Limpar filtros
                    </button>
                </div>
            </section>

            <!-- Filtros globais -->
            <section
                class="grid grid-cols-1 gap-3 rounded-lg border bg-white p-4 md:grid-cols-2 lg:grid-cols-4"
            >
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-600"
                        >Buscar paciente ou aluno</label
                    >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Nome..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600"
                        >Clínica</label
                    >
                    <select
                        v-model="filterClinic"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="all">Todas</option>
                        <option
                            v-for="c in clinicOptions.filter(
                                (x) => x !== 'all',
                            )"
                            :key="c"
                            :value="c"
                        >
                            {{ c }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600"
                        >Período acadêmico</label
                    >
                    <select
                        v-model="filterPeriod"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="all">Todos</option>
                        <option
                            v-for="p in periodOptions.filter(
                                (x) => x !== 'all',
                            )"
                            :key="p"
                            :value="p"
                        >
                            {{ p }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-4">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-gray-600"
                            >Status (nenhum = todos)</span
                        >
                        <button
                            type="button"
                            class="text-xs text-sky-600 hover:underline"
                            @click="clearStatusFilter"
                        >
                            Limpar status
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="s in STATUS_LIST"
                            :key="s"
                            type="button"
                            class="rounded-full border px-2 py-1 text-xs transition"
                            :class="
                                filterStatuses.includes(s)
                                    ? 'border-sky-600 bg-sky-50 text-sky-800'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                            "
                            @click="toggleStatus(s)"
                        >
                            {{ s }}
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
                </div>
                <!-- FILTROS AVANÇADOS -->
                <div v-if="showAdvancedFilters" class="contents">
                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-gray-600"
                        >
                            Data inicial
                        </label>
                        <input
                            v-model="filterDateFrom"
                            type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-medium text-gray-600"
                        >
                            Data final
                        </label>
                        <input
                            v-model="filterDateTo"
                            type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-1 block text-xs font-medium text-gray-600"
                        >
                            Responsável
                        </label>
                        <select
                            v-model="filterResponsible"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                        >
                            <option value="all">Todos</option>
                            <option
                                v-for="resp in responsibleOptions.filter(
                                    (x) => x !== 'all',
                                )"
                                :key="resp"
                                :value="resp"
                            >
                                {{ resp }}
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Visão geral -->
            <template v-if="selectedTab === 'overview'">
                <section
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5"
                >
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Total (filtro)</p>
                        <p class="text-2xl font-bold">{{ kpis.total }}</p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">
                            Taxa de comparecimento
                        </p>
                        <p class="text-2xl font-bold text-emerald-700">
                            {{ fmtPct(kpis.taxaComparecimento) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            (Atendido + Atendido/Atrasado) / (esses + Não
                            Compareceu + Cancelou), só agendamentos
                            <strong>já passados</strong>
                        </p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">
                            Conversão Agendado → Confirmado
                        </p>
                        <p class="text-2xl font-bold text-sky-700">
                            {{ fmtPct(kpis.conversaoAgendadoConfirmado) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Confirmado / (Agendado + Confirmado).
                        </p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">
                            % Atendido/Atrasado (entre atendimentos)
                        </p>
                        <p class="text-2xl font-bold text-teal-700">
                            {{ fmtPct(kpis.pctAtraso) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Atendido/Atrasado / (Atendido + Atendido/Atrasado).
                        </p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Agendado incorreto</p>
                        <p class="text-2xl font-bold text-yellow-800">
                            {{ kpis.incorreto }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Contagem no conjunto filtrado.
                        </p>
                    </div>
                </section>

                <div
                    v-if="!filtered.length"
                    class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-600"
                >
                    Nenhum registro com os filtros atuais.
                </div>

                <div v-else class="overflow-auto rounded-lg border bg-white">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50 text-left">
                                <th class="px-3 py-2">Paciente</th>
                                <th class="px-3 py-2">Aluno</th>
                                <th class="px-3 py-2">Clínica</th>
                                <th class="px-3 py-2">Período</th>
                                <th class="px-3 py-2">Responsável</th>
                                <th class="px-3 py-2">Data</th>
                                <th class="px-3 py-2">Hora</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in filtered"
                                :key="row.id"
                                class="border-b last:border-0"
                            >
                                <td class="px-3 py-2">{{ row.patient }}</td>
                                <td class="px-3 py-2">{{ row.student }}</td>
                                <td class="px-3 py-2">{{ row.clinic }}</td>
                                <td class="px-3 py-2">{{ row.periodLabel }}</td>
                                <td class="px-3 py-2">{{ row.responsible }}</td>
                                <td class="px-3 py-2">{{ row.date }}</td>
                                <td class="px-3 py-2">{{ row.time }}</td>
                                <td class="px-3 py-2">
                                    <span
                                        class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="statusBadgeClass(row.status)"
                                    >
                                        {{ row.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- Por aluno -->
            <section
                v-else-if="selectedTab === 'productivity'"
                class="overflow-auto rounded-lg border bg-white"
            >
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left">
                            <th class="px-3 py-2">Aluno</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Realizados</th>
                            <th class="px-3 py-2">Perdidos</th>
                            <th class="px-3 py-2">Encaixe</th>
                            <th class="px-3 py-2">Agendado incorreto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in productivityByStudent"
                            :key="item.student"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ item.student }}</td>
                            <td class="px-3 py-2">{{ item.total }}</td>
                            <td class="px-3 py-2 text-emerald-700">
                                {{ item.realizados }}
                            </td>
                            <td class="px-3 py-2 text-red-700">
                                {{ item.perdidos }}
                            </td>
                            <td class="px-3 py-2 text-purple-700">
                                {{ item.encaixe }}
                            </td>
                            <td class="px-3 py-2 text-yellow-800">
                                {{ item.incorreto }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Por clínica -->
            <section
                v-else-if="selectedTab === 'clinics'"
                class="overflow-auto rounded-lg border bg-white"
            >
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left">
                            <th class="px-3 py-2">Clínica</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Realizados</th>
                            <th class="px-3 py-2">Perdidos</th>
                            <th class="px-3 py-2">Encaixe</th>
                            <th class="px-3 py-2">Incorreto</th>
                            <th class="px-3 py-2">Rem. paciente</th>
                            <th class="px-3 py-2">Rem. aluno</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in clinicsAggregate"
                            :key="item.clinic"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ item.clinic }}</td>
                            <td class="px-3 py-2">{{ item.total }}</td>
                            <td class="px-3 py-2">{{ item.realizados }}</td>
                            <td class="px-3 py-2">{{ item.perdidos }}</td>
                            <td class="px-3 py-2">{{ item.encaixe }}</td>
                            <td class="px-3 py-2">{{ item.incorreto }}</td>
                            <td class="px-3 py-2">{{ item.remPac }}</td>
                            <td class="px-3 py-2">{{ item.remAlu }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Remarcações -->
            <section
                v-else-if="selectedTab === 'remarcacoes'"
                class="overflow-auto rounded-lg border bg-white"
            >
                <p
                    v-if="!remarcacoesByClinicPeriod.length"
                    class="p-6 text-sm text-gray-500"
                >
                    Nenhuma remarcação no filtro atual.
                </p>
                <table v-else class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left">
                            <th class="px-3 py-2">Clínica</th>
                            <th class="px-3 py-2">Período</th>
                            <th class="px-3 py-2">Remarcado (Paciente)</th>
                            <th class="px-3 py-2">Remarcado (Aluno)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, idx) in remarcacoesByClinicPeriod"
                            :key="idx"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ item.clinic }}</td>
                            <td class="px-3 py-2">{{ item.period }}</td>
                            <td class="px-3 py-2">{{ item.paciente }}</td>
                            <td class="px-3 py-2">{{ item.aluno }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Incorretos por semana -->
            <section v-else class="overflow-auto rounded-lg border bg-white">
                <p
                    v-if="!incorretoByWeek.length"
                    class="p-6 text-sm text-gray-500"
                >
                    Nenhum &quot;Agendado Incorreto&quot; no filtro (por data de
                    criação).
                </p>
                <table v-else class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left">
                            <th class="px-3 py-2">Semana (criação)</th>
                            <th class="px-3 py-2">Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in incorretoByWeek"
                            :key="item.week"
                            class="border-b"
                        >
                            <td class="px-3 py-2">{{ item.week }}</td>
                            <td class="px-3 py-2">{{ item.count }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </AppLayout>
</template>
