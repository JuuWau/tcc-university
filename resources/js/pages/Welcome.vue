<script setup lang="ts">
import { initialPage, login } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    ClipboardList,
    GraduationCap,
    LogIn,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
</script>

<template>
    <Head title="Acadent" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <header
            class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/90 backdrop-blur"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <img
                        src="/favicon.png"
                        alt="Acadent"
                        class="h-10 w-10"
                    />

                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-sky-600">
                            Acadent
                        </h1>

                        <p class="hidden text-xs text-slate-400 sm:block">
                            Gestão de clínicas odontológicas
                        </p>
                    </div>
                </div>

                <nav>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="initialPage()"
                        class="inline-flex items-center rounded-lg border border-sky-600 px-5 py-2.5 text-sm font-semibold text-sky-600 transition hover:bg-sky-50"
                    >
                        Acessar sistema
                    </Link>

                    <Link
                        v-else
                        :href="login()"
                        class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 hover:shadow"
                    >
                        <LogIn class="h-4 w-4" />
                        Entrar
                    </Link>
                </nav>
            </div>
        </header>

        <section
            class="relative overflow-hidden bg-white"
        >
            <div
                class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-sky-100/70 blur-3xl"
            />

            <div
                class="absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-blue-100/60 blur-3xl"
            />

            <div
                class="relative mx-auto grid max-w-7xl items-center gap-16 px-6 py-24 lg:grid-cols-2 lg:px-8 lg:py-32"
            >
                <div>
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700"
                    >
                        <ShieldCheck class="h-4 w-4" />
                        Gestão acadêmica e clínica
                    </div>

                    <h2
                        class="max-w-2xl text-4xl leading-[1.1] font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl"
                    >
                        Gestão inteligente para
                        <span class="text-sky-600">
                            clínicas odontológicas universitárias
                        </span>
                    </h2>

                    <p
                        class="mt-6 max-w-xl text-lg leading-8 text-slate-600"
                    >
                        Centralize alunos, pacientes, atendimentos, agendas e
                        períodos acadêmicos em um único sistema moderno,
                        organizado e seguro.
                    </p>

                    <div class="mt-8">
                        <Link
                            :href="login()"
                            class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-7 py-3.5 font-semibold text-white shadow-lg shadow-sky-600/20 transition hover:-translate-y-0.5 hover:bg-sky-700 hover:shadow-xl"
                        >
                            Acessar o sistema
                        </Link>
                    </div>

                    <div
                        class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-500"
                    >
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="h-4 w-4 text-sky-600" />
                            Acesso seguro
                        </div>

                        <div class="flex items-center gap-2">
                            <ClipboardList class="h-4 w-4 text-sky-600" />
                            Gestão centralizada
                        </div>

                        <div class="flex items-center gap-2">
                            <Users class="h-4 w-4 text-sky-600" />
                            Gestão de usuários
                        </div>
                    </div>
                </div>

                <!-- PAINEL VISUAL -->
                <div class="relative hidden lg:block">
                    <div
                        class="relative mx-auto max-w-lg rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl shadow-slate-300/40"
                    >
                        <div
                            class="rounded-2xl bg-gradient-to-br from-sky-600 to-blue-700 p-7 text-white"
                        >
                            <div class="mb-8 flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-sky-100">
                                        Acadent
                                    </p>

                                    <p class="mt-1 text-2xl font-bold">
                                        Gestão integrada
                                    </p>
                                </div>

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15"
                                >
                                    <GraduationCap class="h-6 w-6" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-xl bg-white/10 p-4 backdrop-blur"
                                >
                                    <Users class="mb-3 h-5 w-5" />

                                    <p class="text-2xl font-bold">
                                        Alunos
                                    </p>

                                    <p class="mt-1 text-xs text-sky-100">
                                        Gestão acadêmica
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-white/10 p-4 backdrop-blur"
                                >
                                    <ClipboardList class="mb-3 h-5 w-5" />

                                    <p class="text-2xl font-bold">
                                        Pacientes
                                    </p>

                                    <p class="mt-1 text-xs text-sky-100">
                                        Histórico clínico
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-white/10 p-4 backdrop-blur"
                                >
                                    <CalendarDays class="mb-3 h-5 w-5" />

                                    <p class="text-2xl font-bold">
                                        Agenda
                                    </p>

                                    <p class="mt-1 text-xs text-sky-100">
                                        Atendimentos
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-white/10 p-4 backdrop-blur"
                                >
                                    <ShieldCheck class="mb-3 h-5 w-5" />

                                    <p class="text-2xl font-bold">
                                        Segurança
                                    </p>

                                    <p class="mt-1 text-xs text-sky-100">
                                        Controle de acesso
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-slate-200 bg-slate-50 py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p
                        class="text-sm font-semibold tracking-wide text-sky-600 uppercase"
                    >
                        Uma solução completa
                    </p>

                    <h3
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Tudo em um único lugar
                    </h3>

                    <p class="mt-4 text-lg text-slate-600">
                        Ferramentas pensadas para simplificar a rotina
                        acadêmica e clínica.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 md:grid-cols-3">
                    <div
                        class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition group-hover:bg-sky-600 group-hover:text-white"
                        >
                            <GraduationCap class="h-6 w-6" />
                        </div>

                        <h4 class="text-lg font-semibold text-slate-900">
                            Gestão Acadêmica
                        </h4>

                        <p class="mt-3 leading-6 text-slate-600">
                            Controle períodos, especialidades, alunos e seus
                            vínculos com a clínica-escola.
                        </p>
                    </div>

                    <div
                        class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition group-hover:bg-sky-600 group-hover:text-white"
                        >
                            <ClipboardList class="h-6 w-6" />
                        </div>

                        <h4 class="text-lg font-semibold text-slate-900">
                            Gestão Clínica
                        </h4>

                        <p class="mt-3 leading-6 text-slate-600">
                            Organize pacientes, procedimentos, atendimentos e
                            histórico clínico de forma centralizada.
                        </p>
                    </div>

                    <div
                        class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600 transition group-hover:bg-sky-600 group-hover:text-white"
                        >
                            <CalendarDays class="h-6 w-6" />
                        </div>

                        <h4 class="text-lg font-semibold text-slate-900">
                            Agenda e Atendimentos
                        </h4>

                        <p class="mt-3 leading-6 text-slate-600">
                            Facilite o planejamento dos atendimentos e o
                            acompanhamento das atividades clínicas.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white py-24">
            <div
                class="mx-auto flex max-w-5xl flex-col items-center px-6 text-center lg:px-8"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-50 text-sky-600"
                >
                    <ShieldCheck class="h-8 w-8" />
                </div>

                <h3
                    class="mt-6 text-3xl font-bold tracking-tight text-slate-900"
                >
                    Organização, segurança e controle
                </h3>

                <p
                    class="mt-4 max-w-2xl text-lg leading-8 text-slate-600"
                >
                    O Acadent centraliza as informações da clínica-escola,
                    permitindo controlar acessos e organizar os processos
                    acadêmicos e clínicos em um ambiente único.
                </p>

                <div
                    class="mt-10 grid w-full gap-4 text-left sm:grid-cols-3"
                >
                    <div class="rounded-xl bg-slate-50 p-5">
                        <ShieldCheck class="h-5 w-5 text-sky-600" />

                        <p class="mt-3 font-semibold text-slate-900">
                            Controle de acesso
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Permissões organizadas por perfil.
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-5">
                        <Users class="h-5 w-5 text-sky-600" />

                        <p class="mt-3 font-semibold text-slate-900">
                            Usuários organizados
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Alunos, professores e equipe administrativa.
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-5">
                        <ClipboardList class="h-5 w-5 text-sky-600" />

                        <p class="mt-3 font-semibold text-slate-900">
                            Informações centralizadas
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Dados acessíveis em um único ambiente.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-6 py-20 lg:px-8">
            <div
                class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-gradient-to-r from-sky-600 to-blue-700 px-8 py-14 text-center shadow-xl sm:px-12"
            >
                <h3
                    class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                >
                    Simplifique a gestão da sua clínica-escola
                </h3>

                <p
                    class="mx-auto mt-4 max-w-2xl text-lg leading-7 text-sky-100"
                >
                    Tenha uma visão centralizada dos processos acadêmicos e
                    clínicos da sua instituição.
                </p>

                <div class="mt-8">
                    <Link
                        :href="login()"
                        class="inline-flex items-center rounded-lg bg-white px-7 py-3.5 font-semibold text-sky-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-sky-50"
                    >
                        Acessar o sistema
                    </Link>
                </div>
            </div>
        </section>

        <footer class="border-t border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-slate-500 sm:flex-row lg:px-8"
            >
                <div class="flex items-center gap-2">
                    <img
                        src="/favicon.png"
                        alt="Acadent"
                        class="h-7 w-7"
                    />

                    <span>
                        © {{ new Date().getFullYear() }} Acadent
                    </span>
                </div>

                <span>
                    Gestão de clínicas odontológicas universitárias
                </span>
            </div>
        </footer>
    </div>
</template>
