<template>
    <div
        class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white/80 p-6 shadow-sm backdrop-blur sm:flex-row sm:items-center"
    >
        <div
            class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-2xl font-semibold text-white shadow"
        >
            {{ initials }}
        </div>

        <div class="min-w-0 flex-1">
            <h1
                class="truncate text-2xl font-semibold tracking-tight text-gray-900"
            >
                {{ student?.person?.name ?? '—' }}
            </h1>

            <div
                class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-500"
            >
                <span>
                    <strong class="font-medium text-gray-700">Período:</strong>
                    {{ currentPeriodLabel }}
                </span>
                <span>
                    <strong class="font-medium text-gray-700">Registro:</strong>
                    {{ student?.registration ?? '—' }}
                </span>
            </div>
        </div>

        <div
            class="relative flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center"
        >
            <Button
                v-if="can('students.personal-page.updateHeaderData')"
                variant="outline"
                size="sm"
                class="w-full sm:w-auto cursor-pointer"
                @click="openEditAcademicDataModal"
            >
                <Pencil class="mr-2 h-4 w-4" />
                Editar dados acadêmicos
            </Button>

            <div class="relative w-full sm:w-auto">
                <button
                    @click="toggleContact"
                    class="flex w-full items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 sm:w-auto sm:justify-center"
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
                        v-if="email"
                        :href="`mailto:${email}`"
                        target="_blank"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <MailPlus class="h-4 w-4" />
                        Enviar email
                    </a>

                    <a
                        v-if="whatsappLink"
                        :href="whatsappLink"
                        target="_blank"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <PhoneCall class="h-4 w-4" />
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { type StudentTabContext, StudentTabContextKey, } from '@/keys/students/studentKeys';
import { usePage } from '@inertiajs/vue3';
import { ChevronDown, MailPlus, Pencil, PhoneCall } from 'lucide-vue-next';
import { computed, inject, ref } from 'vue';

const page = usePage();

const ctx = inject(StudentTabContextKey) as StudentTabContext | undefined;

const can = (permission: string) => {
    return page.props.auth.permissions.includes(permission);
};

if (!ctx) {
    throw new Error(
        'StudentHeader must be used inside a StudentTab (provide StudentTabContextKey).',
    );
}

const student = ctx.student;
const { academicDataEditModalOpen } = ctx;
const contactOpen = ref(false);

function toggleContact() {
    contactOpen.value = !contactOpen.value;
}

const email = computed(() => student?.value?.user?.email ?? null);

const whatsappLink = computed(() => {
    const phone = student?.value?.person?.phone;
    if (!phone) return null;

    const clean = phone.replace(/\D/g, '');

    const withDDI = clean.startsWith('55') ? clean : `55${clean}`;

    return `https://wa.me/${withDDI}`;
});

function openEditAcademicDataModal() {
    academicDataEditModalOpen.value = true;
}
console.log(
    'students.personal-page.updateHeaderData:',
    page.props.auth.permissions.includes('students.personal-page.updateHeaderData'),
);
const initials = computed(() => {
    const name = student?.value?.person?.name?.trim();
    if (!name) return '?';
    const parts = name.split(/\s+/);
    return parts.length >= 2
        ? `${parts[0][0]}${parts.at(-1)?.[0]}`.toUpperCase()
        : name[0].toUpperCase();
});

const currentPeriodLabel = computed(() => {
    const periods = student?.value?.periods ?? [];
    const withPivot = periods as Array<{
        pivot?: { is_current?: boolean };
        academic_year: number;
        semester: number;
        calendar_year: number;
    }>;
    const current = withPivot.find((p) => p.pivot?.is_current) ?? periods[0];

    if (!current) return '—';

    return `${current.academic_year}º ano ${current.semester}º semestre ${current.calendar_year}`;
});
</script>
