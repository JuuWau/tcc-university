<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

type Status = 'active' | 'inactive' | 'abandoned';

interface PatientRow {
    id: number;
    name: string;
    record: string;
    clinic: string;
    lastVisit: string;
    status: Status;
    visits: number;
    createdAt: string;
}

const rows = ref<PatientRow[]>([
    {
        id: 1,
        name: 'Maria Silva',
        record: 'P-001',
        clinic: 'Clínica A',
        lastVisit: '2026-04-01',
        status: 'active',
        visits: 5,
        createdAt: '2026-01-10',
    },
    {
        id: 2,
        name: 'João Pereira',
        record: 'P-002',
        clinic: 'Clínica B',
        lastVisit: '2025-12-10',
        status: 'abandoned',
        visits: 2,
        createdAt: '2025-10-01',
    },
    {
        id: 3,
        name: 'Carla Mendes',
        record: 'P-003',
        clinic: 'Clínica A',
        lastVisit: '2026-03-20',
        status: 'active',
        visits: 3,
        createdAt: '2026-02-05',
    },
    {
        id: 4,
        name: 'Lucas Rocha',
        record: 'P-004',
        clinic: 'Clínica C',
        lastVisit: '2026-01-15',
        status: 'inactive',
        visits: 1,
        createdAt: '2025-11-20',
    },
]);

const statusFilter = ref<'all' | Status>('all');
const search = ref('');

const filtered = computed(() => {
    return rows.value.filter((row) => {
        const byStatus =
            statusFilter.value === 'all' || row.status === statusFilter.value;

        const term = search.value.toLowerCase();
        const bySearch =
            !term ||
            row.name.toLowerCase().includes(term) ||
            row.record.includes(term);

        return byStatus && bySearch;
    });
});

const kpis = computed(() => ({
    total: rows.value.length,
    active: rows.value.filter((r) => r.status === 'active').length,
    inactive: rows.value.filter((r) => r.status === 'inactive').length,
    abandoned: rows.value.filter((r) => r.status === 'abandoned').length,
}));

const statusLabel = {
    active: 'Ativo',
    inactive: 'Inativo',
    abandoned: 'Abandonou',
};

type ReportTab =
    | 'status'
    | 'frequency'
    | 'clinics'
    | 'risk'
    | 'dataQuality'
    | 'operational';

const selectedTab = ref<ReportTab>('status');

const clinicDistribution = [
    { clinic: 'Clínica A', patients: 20 },
    { clinic: 'Clínica B', patients: 15 },
    { clinic: 'Clínica C', patients: 8 },
];

const topPatients = [
    { name: 'Maria Silva', visits: 5 },
    { name: 'Carla Mendes', visits: 3 },
];

const riskPatients = [
    { name: 'João Pereira', lastVisit: '2025-12-10', days: 120 },
];

const dataQuality = [
    { metric: 'Sem telefone', value: 10, percent: '8%' },
    { metric: 'Sem CPF', value: 5, percent: '4%' },
];

const monthlyEvolution = [
    { month: 'Jan', newPatients: 10 },
    { month: 'Fev', newPatients: 7 },
    { month: 'Mar', newPatients: 12 },
];

const isExporting = ref(false);

function downloadCsv(filename: string, headers: string[], rows: any[][]) {
    const csv = [headers.join(','), ...rows.map((r) => r.join(','))].join('\n');

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
}

function exportCurrentReport() {
    isExporting.value = true;

    if (selectedTab.value === 'status') {
        downloadCsv(
            'relatorio-pacientes.csv',
            ['Nome', 'Prontuário', 'Clínica', 'Última Consulta', 'Status'],
            filtered.value.map((r) => [
                r.name,
                r.record,
                r.clinic,
                r.lastVisit,
                statusLabel[r.status],
            ]),
        );
    }

    isExporting.value = false;
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <!-- HEADER -->
            <section class="space-y-4 rounded-lg border bg-white p-4">
                <div>
                    <h1 class="text-2xl font-semibold">
                        Relatório de Pacientes
                    </h1>
                    <p class="text-sm text-gray-600">
                        Visão geral dos pacientes atendidos
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <select
                        v-model="selectedTab"
                        class="rounded border px-3 py-2 text-sm"
                    >
                        <option value="status">Status</option>
                        <option value="frequency">Frequência</option>
                        <option value="clinics">Por Clínica</option>
                        <option value="risk">Sem retorno</option>
                        <option value="dataQuality">Qualidade cadastral</option>
                        <option value="operational">Operacional</option>
                    </select>

                    <button
                        @click="exportCurrentReport"
                        class="rounded bg-sky-600 px-3 py-2 text-sm text-white"
                    >
                        Exportar
                    </button>
                </div>
            </section>

            <!-- STATUS -->
            <section v-if="selectedTab === 'status'" class="space-y-4">
                <div class="grid grid-cols-4 gap-4">
                    <div class="rounded border bg-white p-4">
                        <p>Total</p>
                        <p class="text-2xl font-bold">{{ kpis.total }}</p>
                    </div>

                    <div class="rounded border bg-white p-4">
                        <p>Ativos</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ kpis.active }}
                        </p>
                    </div>

                    <div class="rounded border bg-white p-4">
                        <p>Inativos</p>
                        <p class="text-2xl font-bold text-gray-600">
                            {{ kpis.inactive }}
                        </p>
                    </div>

                    <div class="rounded border bg-white p-4">
                        <p>Abandonaram</p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ kpis.abandoned }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 space-y-4 rounded-lg border bg-white p-4">
                    <input
                        v-model="search"
                        placeholder="Buscar paciente..."
                        class="w-full rounded border px-3 py-2"
                    />

                    <select
                        v-model="statusFilter"
                        class="rounded border px-3 py-2"
                    >
                        <option value="all">Todos</option>
                        <option value="active">Ativos</option>
                        <option value="inactive">Inativos</option>
                        <option value="abandoned">Abandonaram</option>
                    </select>
                </div>

                <div class="overflow-auto rounded border bg-white">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="p-2">Nome</th>
                                <th class="p-2">Clínica</th>
                                <th class="p-2">Última consulta</th>
                                <th class="p-2">Atendimentos</th>
                                <th class="p-2">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="row in filtered"
                                :key="row.id"
                                class="border-b"
                            >
                                <td class="p-2">{{ row.name }}</td>
                                <td class="p-2">{{ row.clinic }}</td>
                                <td class="p-2">{{ row.lastVisit }}</td>
                                <td class="p-2">{{ row.visits }}</td>

                                <td class="p-2">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-700':
                                                row.status === 'active',
                                            'bg-gray-100 text-gray-700':
                                                row.status === 'inactive',
                                            'bg-red-100 text-red-700':
                                                row.status === 'abandoned',
                                        }"
                                        class="rounded-full px-2 py-1 text-xs"
                                    >
                                        {{ statusLabel[row.status] }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- FREQUENCIA -->
            <section
                v-else-if="selectedTab === 'frequency'"
                class="rounded border bg-white p-4"
            >
                <h2 class="mb-2 font-semibold">Pacientes mais atendidos</h2>
                <div
                    v-for="p in topPatients"
                    :key="p.name"
                    class="flex justify-between border-b py-2"
                >
                    <span>{{ p.name }}</span>
                    <span
                        class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-700"
                    >
                        {{ p.visits }} atendimentos
                    </span>
                </div>
            </section>

            <!-- CLINICAS -->
            <section
                v-else-if="selectedTab === 'clinics'"
                class="rounded border bg-white p-4"
            >
                <div
                    v-for="c in clinicDistribution"
                    :key="c.clinic"
                    class="flex justify-between border-b py-2"
                >
                    <span>{{ c.clinic }}</span>
                    <span>{{ c.patients }} pacientes</span>
                </div>
            </section>

            <!-- RISCO -->
            <section
                v-else-if="selectedTab === 'risk'"
                class="rounded border bg-white p-4"
            >
                <h2 class="mb-2 font-semibold">Pacientes sem retorno</h2>
                <div
                    v-for="p in riskPatients"
                    :key="p.name"
                    class="flex justify-between border-b py-2"
                >
                    <span>{{ p.name }}</span>
                    <span class="text-red-600">{{ p.days }} dias</span>
                </div>
            </section>

            <!-- QUALIDADE -->
            <section
                v-else-if="selectedTab === 'dataQuality'"
                class="grid grid-cols-2 gap-4"
            >
                <div
                    v-for="d in dataQuality"
                    :key="d.metric"
                    class="rounded border bg-white p-4"
                >
                    <p class="text-sm">{{ d.metric }}</p>
                    <p class="text-xl font-bold">{{ d.value }}</p>
                    <p class="text-xs text-gray-500">{{ d.percent }}</p>
                </div>
            </section>

            <!-- OPERACIONAL -->
            <section v-else class="rounded border bg-white p-4">
                <h2 class="mb-2 font-semibold">Evolução mensal</h2>
                <div
                    v-for="m in monthlyEvolution"
                    :key="m.month"
                    class="flex justify-between border-b py-2"
                >
                    <span>{{ m.month }}</span>
                    <span>{{ m.newPatients }} novos</span>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
