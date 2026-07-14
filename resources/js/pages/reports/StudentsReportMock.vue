<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
type Status = 'active' | 'pending' | 'inactive';

interface StudentRow {
    id: number;
    name: string;
    registration: string;
    period: string;
    status: Status;
    inviteUsed: boolean;
    createdAt: string;
}

const rows = ref<StudentRow[]>([
    {
        id: 1,
        name: 'Ana Souza',
        registration: '2023001',
        period: '2o ano / 1o sem',
        status: 'active',
        inviteUsed: true,
        createdAt: '2026-01-10',
    },
    {
        id: 2,
        name: 'Bruno Lima',
        registration: '2023002',
        period: '1o ano / 2o sem',
        status: 'pending',
        inviteUsed: false,
        createdAt: '2026-02-05',
    },
    {
        id: 3,
        name: 'Carla Mendes',
        registration: '2022007',
        period: '3o ano / 1o sem',
        status: 'inactive',
        inviteUsed: true,
        createdAt: '2025-11-20',
    },
    {
        id: 4,
        name: 'Diego Alves',
        registration: '2024015',
        period: '1o ano / 1o sem',
        status: 'active',
        inviteUsed: true,
        createdAt: '2026-03-01',
    },
    {
        id: 5,
        name: 'Elaine Rocha',
        registration: '2024018',
        period: '1o ano / 1o sem',
        status: 'pending',
        inviteUsed: false,
        createdAt: '2026-03-10',
    },
]);

const statusFilter = ref<'all' | Status>('all');
const search = ref('');

const filtered = computed(() => {
    return rows.value.filter((row) => {
        const byStatus =
            statusFilter.value === 'all' || row.status === statusFilter.value;
        const term = search.value.trim().toLowerCase();
        const bySearch =
            !term ||
            row.name.toLowerCase().includes(term) ||
            row.registration.includes(term);

        return byStatus && bySearch;
    });
});

const kpis = computed(() => {
    const total = rows.value.length;
    const active = rows.value.filter((r) => r.status === 'active').length;
    const pending = rows.value.filter((r) => r.status === 'pending').length;
    const inactive = rows.value.filter((r) => r.status === 'inactive').length;
    return { total, active, pending, inactive };
});

const statusLabel: Record<Status, string> = {
    active: 'Ativo',
    pending: 'Pendente',
    inactive: 'Inativo',
};

type ReportTab =
    | 'status'
    | 'engagement'
    | 'academic'
    | 'movements'
    | 'dataQuality'
    | 'operational';

const selectedTab = ref<ReportTab>('status');
const periodFilter = ref('Todos');
const dateRangeFilter = ref('Ultimos 90 dias');

const inviteSummary = {
    sent: 128,
    used: 92,
    expired: 21,
    pending: 15,
    activationRate: '71,9%',
    avgAcceptanceHours: '36h',
};

const academicDistribution = [
    { label: '1o ano / 1o sem', students: 42 },
    { label: '1o ano / 2o sem', students: 29 },
    { label: '2o ano / 1o sem', students: 24 },
    { label: '2o ano / 2o sem', students: 17 },
    { label: '3o ano / 1o sem', students: 11 },
];

const periodChangeHistory = [
    {
        student: 'Ana Souza',
        from: '1o ano / 2o sem',
        to: '2o ano / 1o sem',
        changedAt: '2026-01-15',
    },
    {
        student: 'Bruno Lima',
        from: '1o ano / 1o sem',
        to: '1o ano / 2o sem',
        changedAt: '2026-02-22',
    },
    {
        student: 'Diego Alves',
        from: 'Sem periodo',
        to: '1o ano / 1o sem',
        changedAt: '2026-03-01',
    },
];

const movements = [
    {
        type: 'Cadastro',
        student: 'Elaine Rocha',
        date: '2026-03-10',
        reason: '-',
        note: '-',
    },
    {
        type: 'Desativacao',
        student: 'Carla Mendes',
        date: '2026-02-18',
        reason: 'Trancamento',
        note: 'Solicitacao da aluna',
    },
    {
        type: 'Reativacao',
        student: 'Carla Mendes',
        date: '2026-03-03',
        reason: 'Retorno',
        note: 'Retomou atividades',
    },
    {
        type: 'Exclusao',
        student: 'Joao Pinto',
        date: '2026-01-30',
        reason: 'Duplicidade',
        note: 'Registro duplicado removido',
    },
];

const dataQuality = [
    { metric: 'Sem CPF', value: 14, percent: '10,9%' },
    { metric: 'Sem telefone', value: 22, percent: '17,2%' },
    { metric: 'Sem endereco completo', value: 35, percent: '27,3%' },
    { metric: 'Sem data de nascimento', value: 9, percent: '7,0%' },
];

const monthlyEvolution = [
    { month: 'Jan/2026', newStudents: 12, inactive: 2 },
    { month: 'Fev/2026', newStudents: 9, inactive: 4 },
    { month: 'Mar/2026', newStudents: 15, inactive: 1 },
];

const isExporting = ref(false);

function escapeCsvValue(value: string | number): string {
    const stringValue = String(value);
    if (
        stringValue.includes(',') ||
        stringValue.includes('"') ||
        stringValue.includes('\n')
    ) {
        return `"${stringValue.replace(/"/g, '""')}"`;
    }
    return stringValue;
}

function downloadCsv(
    filename: string,
    headers: string[],
    rows: (string | number)[][],
) {
    const csvContent = [
        headers.map((header) => escapeCsvValue(header)).join(','),
        ...rows.map((row) => row.map((cell) => escapeCsvValue(cell)).join(',')),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function exportCurrentReport() {
    isExporting.value = true;

    const timestamp = new Date().toISOString().slice(0, 10);

    if (selectedTab.value === 'status') {
        downloadCsv(
            `relatorio-status-estudantes-${timestamp}.csv`,
            ['Nome', 'RA', 'Periodo', 'Status', 'Convite', 'Cadastro'],
            filtered.value.map((row) => [
                row.name,
                row.registration,
                row.period,
                statusLabel[row.status],
                row.inviteUsed ? 'Aceito' : 'Pendente',
                row.createdAt,
            ]),
        );
    } else if (selectedTab.value === 'engagement') {
        downloadCsv(
            `relatorio-adesao-estudantes-${timestamp}.csv`,
            ['Indicador', 'Valor'],
            [
                ['Convites enviados', inviteSummary.sent],
                ['Convites usados', inviteSummary.used],
                ['Convites expirados', inviteSummary.expired],
                ['Convites pendentes', inviteSummary.pending],
                ['Taxa de ativacao', inviteSummary.activationRate],
                ['Tempo medio de aceite', inviteSummary.avgAcceptanceHours],
            ],
        );
    } else if (selectedTab.value === 'academic') {
        downloadCsv(
            `relatorio-academico-estudantes-${timestamp}.csv`,
            ['Secao', 'Campo 1', 'Campo 2', 'Campo 3', 'Campo 4'],
            [
                ...academicDistribution.map((item) => [
                    'Distribuicao por periodo',
                    item.label,
                    item.students,
                    '',
                    '',
                ]),
                ...periodChangeHistory.map((item) => [
                    'Historico de mudanca',
                    item.student,
                    item.from,
                    item.to,
                    item.changedAt,
                ]),
            ],
        );
    } else if (selectedTab.value === 'movements') {
        downloadCsv(
            `relatorio-movimentacoes-estudantes-${timestamp}.csv`,
            ['Tipo', 'Aluno', 'Data', 'Motivo', 'Observacao'],
            movements.map((item) => [
                item.type,
                item.student,
                item.date,
                item.reason,
                item.note,
            ]),
        );
    } else if (selectedTab.value === 'dataQuality') {
        downloadCsv(
            `relatorio-qualidade-cadastral-estudantes-${timestamp}.csv`,
            ['Metrica', 'Quantidade', 'Percentual'],
            dataQuality.map((item) => [item.metric, item.value, item.percent]),
        );
    } else {
        downloadCsv(
            `relatorio-operacional-estudantes-${timestamp}.csv`,
            ['Secao', 'Campo', 'Valor'],
            [
                ['Indicador', 'Total de alunos', 128],
                ['Indicador', 'Pendentes 1o acesso', 15],
                ['Indicador', 'Inativos recentes', 5],
                ['Indicador', 'Novos no mes', 15],
                ...monthlyEvolution.map((item) => [
                    'Evolucao mensal',
                    `${item.month} - entradas`,
                    item.newStudents,
                ]),
                ...monthlyEvolution.map((item) => [
                    'Evolucao mensal',
                    `${item.month} - inativacoes`,
                    item.inactive,
                ]),
            ],
        );
    }

    isExporting.value = false;
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <section
                class="justify-between space-y-4 rounded-lg border bg-white p-4"
            >
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Relatorio de Estudantes
                    </h1>
                    <p class="text-sm text-gray-600">
                        Referente aos estudantes
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="selectedTab"
                        class="rounded-md border px-3 py-2 text-sm"
                    >
                        <option value="status">Status de alunos</option>
                        <option value="engagement">Adesao ao sistema</option>
                        <option value="academic">Academico por periodo</option>
                        <option value="movements">Movimentacoes</option>
                        <option value="dataQuality">Qualidade cadastral</option>
                        <option value="operational">Operacional</option>
                    </select>

                    <select
                        v-model="periodFilter"
                        class="rounded-md border px-3 py-2 text-sm"
                    >
                        <option>Todos</option>
                        <option>1o ano / 1o sem</option>
                        <option>1o ano / 2o sem</option>
                        <option>2o ano / 1o sem</option>
                        <option>2o ano / 2o sem</option>
                        <option>3o ano / 1o sem</option>
                    </select>

                    <select
                        v-model="dateRangeFilter"
                        class="rounded-md border px-3 py-2 text-sm"
                    >
                        <option>Ultimos 30 dias</option>
                        <option>Ultimos 90 dias</option>
                        <option>Ultimos 12 meses</option>
                    </select>

                    <button
                        type="button"
                        class="rounded-md bg-sky-600 px-3 py-2 text-sm font-medium text-white hover:bg-sky-700 disabled:opacity-50"
                        :disabled="isExporting"
                        @click="exportCurrentReport"
                    >
                        {{ isExporting ? 'Exportando...' : 'Exportar' }}
                    </button>
                </div>
            </section>

            <section v-if="selectedTab === 'status'" class="space-y-4">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold">{{ kpis.total }}</p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Ativos</p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ kpis.active }}
                        </p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Pendentes</p>
                        <p class="text-2xl font-bold text-amber-600">
                            {{ kpis.pending }}
                        </p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Inativos</p>
                        <p class="text-2xl font-bold text-rose-600">
                            {{ kpis.inactive }}
                        </p>
                    </div>
                </div>

                <div class="space-y-4 rounded-lg border bg-white p-4">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nome ou RA..."
                            class="w-full rounded-md border px-3 py-2 text-sm"
                        />
                        <select
                            v-model="statusFilter"
                            class="rounded-md border px-3 py-2 text-sm"
                        >
                            <option value="all">Todos</option>
                            <option value="active">Ativos</option>
                            <option value="pending">Pendentes</option>
                            <option value="inactive">Inativos</option>
                        </select>
                    </div>

                    <div class="overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="py-2 pr-4">Nome</th>
                                    <th class="py-2 pr-4">RA</th>
                                    <th class="py-2 pr-4">Periodo</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Convite</th>
                                    <th class="py-2 pr-4">Cadastro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in filtered"
                                    :key="row.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4">{{ row.name }}</td>
                                    <td class="py-2 pr-4">
                                        {{ row.registration }}
                                    </td>
                                    <td class="py-2 pr-4">{{ row.period }}</td>
                                    <td class="py-2 pr-4">
                                        {{ statusLabel[row.status] }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{
                                            row.inviteUsed
                                                ? 'Aceito'
                                                : 'Pendente'
                                        }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ row.createdAt }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section
                v-else-if="selectedTab === 'engagement'"
                class="grid grid-cols-1 gap-4 md:grid-cols-3"
            >
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm text-gray-500">Convites enviados</p>
                    <p class="text-2xl font-bold">{{ inviteSummary.sent }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm text-gray-500">Convites usados</p>
                    <p class="text-2xl font-bold text-emerald-600">
                        {{ inviteSummary.used }}
                    </p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm text-gray-500">Convites expirados</p>
                    <p class="text-2xl font-bold text-rose-600">
                        {{ inviteSummary.expired }}
                    </p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm text-gray-500">Convites pendentes</p>
                    <p class="text-2xl font-bold text-amber-600">
                        {{ inviteSummary.pending }}
                    </p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm text-gray-500">Taxa de ativacao</p>
                    <p class="text-2xl font-bold">
                        {{ inviteSummary.activationRate }}
                    </p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-sm text-gray-500">Tempo medio de aceite</p>
                    <p class="text-2xl font-bold">
                        {{ inviteSummary.avgAcceptanceHours }}
                    </p>
                </div>
            </section>

            <section v-else-if="selectedTab === 'academic'" class="space-y-4">
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-2 text-lg font-semibold">
                        Distribuicao por periodo
                    </h2>
                    <div class="overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="py-2 pr-4">Periodo</th>
                                    <th class="py-2 pr-4">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in academicDistribution"
                                    :key="item.label"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4">{{ item.label }}</td>
                                    <td class="py-2 pr-4">
                                        {{ item.students }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-2 text-lg font-semibold">
                        Historico de mudanca
                    </h2>
                    <div class="overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="py-2 pr-4">Aluno</th>
                                    <th class="py-2 pr-4">De</th>
                                    <th class="py-2 pr-4">Para</th>
                                    <th class="py-2 pr-4">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in periodChangeHistory"
                                    :key="`${item.student}-${item.changedAt}`"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4">
                                        {{ item.student }}
                                    </td>
                                    <td class="py-2 pr-4">{{ item.from }}</td>
                                    <td class="py-2 pr-4">{{ item.to }}</td>
                                    <td class="py-2 pr-4">
                                        {{ item.changedAt }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section
                v-else-if="selectedTab === 'movements'"
                class="rounded-lg border bg-white p-4"
            >
                <h2 class="mb-2 text-lg font-semibold">
                    Movimentacoes de alunos
                </h2>
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="py-2 pr-4">Tipo</th>
                                <th class="py-2 pr-4">Aluno</th>
                                <th class="py-2 pr-4">Data</th>
                                <th class="py-2 pr-4">Motivo</th>
                                <th class="py-2 pr-4">Observacao</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in movements"
                                :key="`${item.type}-${item.student}-${item.date}`"
                                class="border-b last:border-0"
                            >
                                <td class="py-2 pr-4">{{ item.type }}</td>
                                <td class="py-2 pr-4">{{ item.student }}</td>
                                <td class="py-2 pr-4">{{ item.date }}</td>
                                <td class="py-2 pr-4">{{ item.reason }}</td>
                                <td class="py-2 pr-4">{{ item.note }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section
                v-else-if="selectedTab === 'dataQuality'"
                class="grid grid-cols-1 gap-4 md:grid-cols-2"
            >
                <div
                    v-for="item in dataQuality"
                    :key="item.metric"
                    class="rounded-lg border bg-white p-4"
                >
                    <p class="text-sm text-gray-500">{{ item.metric }}</p>
                    <p class="text-2xl font-bold">{{ item.value }}</p>
                    <p class="text-sm text-gray-600">
                        {{ item.percent }} da base
                    </p>
                </div>
            </section>

            <section v-else class="space-y-4">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Total de alunos</p>
                        <p class="text-2xl font-bold">128</p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Pendentes 1o acesso</p>
                        <p class="text-2xl font-bold text-amber-600">15</p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Inativos recentes</p>
                        <p class="text-2xl font-bold text-rose-600">5</p>
                    </div>
                    <div class="rounded-lg border bg-white p-4">
                        <p class="text-sm text-gray-500">Novos no mes</p>
                        <p class="text-2xl font-bold text-sky-600">15</p>
                    </div>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-2 text-lg font-semibold">Evolucao mensal</h2>
                    <div class="overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="py-2 pr-4">Mes</th>
                                    <th class="py-2 pr-4">Entradas</th>
                                    <th class="py-2 pr-4">Inativacoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in monthlyEvolution"
                                    :key="item.month"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-2 pr-4">{{ item.month }}</td>
                                    <td class="py-2 pr-4">
                                        {{ item.newStudents }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ item.inactive }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
