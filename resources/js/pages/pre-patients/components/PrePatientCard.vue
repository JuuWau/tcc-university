<script setup lang="ts">
import PrePatientStatusBadge from '@/components/badges/PrePatientStatusBadge.vue';
import type { PrePatient } from '@/types/pre-patients/prePatient';

defineProps<{
    prePatient: PrePatient;
}>();

const emit = defineEmits<{
    edit: [prePatient: PrePatient];
    delete: [prePatient: PrePatient];
}>();

// const statusLabel: Record<string, string> = {
//     aguardando: 'Aguardando',
//     convertido: 'Convertido',
//     cancelado: 'Cancelado',
// };
</script>

<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
    >
        <div
            class="flex items-start justify-between gap-3"
        >
            <div class="min-w-0">
                <h3
                    class="truncate font-medium text-gray-900"
                >
                    {{ prePatient.name }}
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    {{
                        prePatient.cpf ||
                        'CPF não informado'
                    }}
                </p>
            </div>

            <PrePatientStatusBadge :value="prePatient.status" />
        </div>

        <div
            class="mt-4 space-y-2 text-sm text-gray-600"
        >
            <div>
                <span class="font-medium text-gray-700">
                    Telefone:
                </span>

                <span>
                    {{
                        prePatient.phone ||
                        'Não informado'
                    }}
                </span>
            </div>

            <div>
                <span class="font-medium text-gray-700">
                    E-mail:
                </span>

                <span>
                    {{
                        prePatient.email ||
                        'Não informado'
                    }}
                </span>
            </div>

            <div>
                <span class="font-medium text-gray-700">
                    Clínicas:
                </span>

                <span>
                    {{
                        prePatient.clinics?.length
                            ? prePatient.clinics
                                .map(
                                    (clinic) =>
                                        clinic.name
                                )
                                .join(', ')
                            : 'Nenhuma'
                    }}
                </span>
            </div>
        </div>

        <div
            class="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-3"
        >
            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100"
                @click="emit('edit', prePatient)"
            >
                Editar
            </button>

            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium text-red-600 transition hover:bg-red-50"
                @click="emit('delete', prePatient)"
            >
                Excluir
            </button>
        </div>
    </div>
</template>