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
                {{ user?.person?.name ?? user?.email ?? '—' }}
            </h1>

            <div
                class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-500"
            >
                <span>
                    <strong class="font-medium text-gray-700">Perfil:</strong>
                    {{ user?.role?.name ?? '—' }}
                </span>
                <span>
                    <strong class="font-medium text-gray-700">E-mail:</strong>
                    {{ user?.email ?? '—' }}
                </span>
            </div>
        </div>

        <div
            class="relative flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center"
        >
            <Button
                variant="outline"
                size="sm"
                class="w-full sm:w-auto cursor-pointer"
                @click="openEditRoleModal"
            >
                <Pencil class="mr-2 h-4 w-4" />
                Editar perfil
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
                        v-if="user?.email"
                        :href="`mailto:${user.email}`"
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
import type { UserTabContext } from '@/keys/users/userKeys';
import { UserTabContextKey } from '@/keys/users/userKeys';
import { ChevronDown, MailPlus, Pencil, PhoneCall } from 'lucide-vue-next';
import { computed, inject, ref } from 'vue';

const ctx = inject(UserTabContextKey) as UserTabContext | undefined;

if (!ctx) {
    throw new Error(
        'UserHeader must be used inside a UserTab (provide UserTabContextKey).',
    );
}

const user = ctx.user;
const { editRoleModalOpen } = ctx;

const contactOpen = ref(false);

function openEditRoleModal() {
    editRoleModalOpen.value = true;
}

function toggleContact() {
    contactOpen.value = !contactOpen.value;
}

const whatsappLink = computed(() => {
    const phone = user?.value?.person?.phone;
    if (!phone) return null;
    const clean = phone.replace(/\D/g, '');
    const withDDI = clean.startsWith('55') ? clean : `55${clean}`;
    return `https://wa.me/${withDDI}`;
});

const initials = computed(() => {
    const name = user?.value?.person?.name?.trim();
    if (name) {
        const parts = name.split(/\s+/);
        return parts.length >= 2
            ? `${parts[0][0]}${parts.at(-1)?.[0]}`.toUpperCase()
            : name[0].toUpperCase();
    }
    const email = user?.value?.email;
    if (email) return email[0].toUpperCase();
    return '?';
});
</script>
