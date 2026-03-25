<template>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div
            class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Dados pessoais
                </h2>
                <p class="text-sm text-gray-500">
                    Dados pessoais do paciente
                </p>
            </div>

            <Button
                variant="outline"
                class="cursor-pointer"
                size="sm"
                @click="openEditModal"
            >
                <Pencil class="mr-2 h-4 w-4" />
                Editar
            </Button>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">
                    Nome completo
                </p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ patient?.name ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">
                    E-mail
                </p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ patient?.email ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">CPF</p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ patient?.cpf ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">
                    Telefone
                </p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ patient?.phone ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs tracking-wide text-gray-400 uppercase">
                    Data de nascimento
                </p>
                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ birthDate }}
                </p>
            </div>
        </div>

        <div
            v-if="patient?.address"
            class="mt-6 border-t border-gray-100 pt-6"
        >
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Endereço</h3>

            <div class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <p class="text-xs tracking-wide text-gray-400 uppercase">
                        Rua
                    </p>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ patient?.address?.street ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs tracking-wide text-gray-400 uppercase">
                        Número
                    </p>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ patient?.address?.number ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs tracking-wide text-gray-400 uppercase">
                        Bairro
                    </p>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ patient?.address?.neighborhood ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs tracking-wide text-gray-400 uppercase">
                        Cidade
                    </p>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ patient?.address?.city ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs tracking-wide text-gray-400 uppercase">
                        Estado
                    </p>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ patient?.address?.state ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs tracking-wide text-gray-400 uppercase">
                        CEP
                    </p>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ patient?.address?.cep ?? '—' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PatientTabContext } from '@/keys/patients/patientKeys';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import { Pencil } from 'lucide-vue-next';
import { computed, inject } from 'vue';

const ctx = inject(PatientTabContextKey) as PatientTabContext | undefined;

if (!ctx) {
    throw new Error(
        'PatientPersonalData must be used inside a PatientTab (provide PatientTabContextKey).',
    );
}

const { patient, editPersonalDataModalOpen } = ctx;

function openEditModal() {
    editPersonalDataModalOpen.value = true;
}

const birthDate = computed(() => {
    const d = patient?.value?.birth_date;
    if (!d) return '—';
    const date = new Date(d);
    return Number.isNaN(date.getTime())
        ? '—'
        : date.toLocaleDateString('pt-BR');
});
</script>
