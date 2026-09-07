<template>
    <div
        class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm backdrop-blur sm:flex-row sm:items-center sm:gap-6 sm:p-6"
    >
        <div
            class="flex h-16 w-16 shrink-0 self-center items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-lg font-semibold text-white shadow sm:h-24 sm:w-24 sm:self-auto sm:text-2xl"
        >
            {{ initials }}
        </div>
        <div class="min-w-0 flex-1">
            <h1
                class="break-words text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl"
            >
                {{ patient?.name ?? '—' }}
            </h1>
            <div
                class="mt-2 flex flex-col gap-1 text-sm text-gray-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-6 sm:gap-y-1"
            >
                <span>
                    <strong class="font-medium text-gray-700"> Código: </strong>
                    {{ patient?.code ?? '—' }}
                </span>
                <span class="break-words">
                    <strong class="font-medium text-gray-700">
                        Estudantes:
                    </strong>
                    {{
                        patient?.students?.map((s) => s.name).join(', ') ?? '—'
                    }}
                </span>
                <span class="flex items-center gap-2">
                    <strong class="font-medium text-gray-700">
                            Status:
                    </strong>

                    <span
                            class="rounded-full px-2.5 py-1 text-xs font-medium"
                            :class="statusClasses"
                    >
                            {{ statusLabel }}
                    </span>
            </span>
            </div>
        </div>
        <div
            class="relative flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center"
        >
            <Button
                v-if="can('patients.personal-page.updateHeaderData')"
                variant="outline"
                size="sm"
                class="w-full cursor-pointer sm:w-auto"
                @click="openEditStudentModal"
            >
                <Pencil class="mr-2 h-4 w-4" /> Editar
            </Button>
            <div class="relative w-full sm:w-auto">
                <button
                    type="button"
                    @click="toggleContact"
                    class="flex w-full self-center items-center justify-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 sm:w-auto sm:justify-center"
                >
                    <span>Contato</span>
                    <ChevronDown
                        class="h-4 w-4 transition"
                        :class="contactOpen ? 'rotate-180' : ''"
                    />
                </button>
                <div
                    v-if="contactOpen"
                    class="absolute right-0 z-20 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg sm:w-48"
                >
                    <a
                        v-if="patient?.email"
                        :href="`mailto:${patient.email}`"
                        target="_blank"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <Mail class="h-4 w-4" /> Enviar email
                    </a>
                    <a
                        v-if="whatsappLink"
                        :href="whatsappLink"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <PhoneCall class="h-4 w-4" /> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PatientTabContext } from '@/keys/patients/patientKeys';
import { PatientTabContextKey } from '@/keys/patients/patientKeys';
import type { PatientStatusKey } from '@/types/patient/patient';
import { PATIENT_STATUS } from '@/types/patient/patient';
import { usePage } from '@inertiajs/vue3';
import { ChevronDown, Mail, Pencil, PhoneCall } from 'lucide-vue-next';
import { computed, inject, ref } from 'vue';

const page = usePage();

const can = (permission: string) => {
    return page.props.auth.permissions.includes(permission);
};

const ctx = inject(PatientTabContextKey) as PatientTabContext | undefined;

if (!ctx) {
    throw new Error(
        'PatientHeader must be used inside a PatientTab (provide PatientTabContextKey).',
    );
}

const patient = ctx.patient;
const { editStudentModalOpen } = ctx;

const contactOpen = ref(false);

const statusMap: Record<
        string,
        {
                label: string;
                classes: string;
        }
> = {
        ativo: {
                label: 'Ativo',
                classes: 'bg-green-200 text-green-900',
        },

        inativo: {
                label: 'Inativo',
                classes: 'bg-slate-300 text-slate-900',
        },

        tratamento: {
                label: 'Tratamento',
                classes: 'bg-sky-200 text-sky-900',
        },

        pausa_tratamento: {
                label: 'Pausa no Tratamento',
                classes: 'bg-amber-200 text-amber-900',
        },

        abandono: {
                label: 'Abandono',
                classes: 'bg-red-200 text-red-900',
        },

        concluido: {
                label: 'Concluído',
                classes: 'bg-teal-300 text-teal-900',
        },

        transferencia: {
                label: 'Transferência',
                classes: 'bg-purple-200 text-purple-900',
        },
};

const statusLabel = computed(() => {
        const key = (patient?.value?.status ?? 'ativo') as PatientStatusKey;

        return statusMap[key]?.label ?? PATIENT_STATUS[key] ?? key;
});

const statusClasses = computed(() => {
        const key = (patient?.value?.status ?? 'ativo') as PatientStatusKey;

        return statusMap[key]?.classes ?? 'bg-slate-200 text-slate-900';
});

function openEditStudentModal() {
    editStudentModalOpen.value = true;
}

function toggleContact() {
    contactOpen.value = !contactOpen.value;
}

const whatsappLink = computed(() => {
    const phone = patient?.value?.phone;
    if (!phone) return null;
    const clean = phone.replace(/\D/g, '');
    const withDDI = clean.startsWith('55') ? clean : `55${clean}`;
    return `https://wa.me/${withDDI}`;
});

const initials = computed(() => {
    const name = patient?.value?.name?.trim();
    if (name) {
        const parts = name.split(/\s+/);
        return parts.length >= 2
            ? `${parts[0][0]}${parts.at(-1)?.[0]}`.toUpperCase()
            : name[0].toUpperCase();
    }
    const email = patient?.value?.email;
    if (email) return email[0].toUpperCase();
    return '?';
});
</script>
