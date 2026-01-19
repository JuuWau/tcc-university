<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Sistema Odontológico Universitário" />

    <div class="min-h-screen bg-white text-gray-800">
        <!-- HEADER -->
        <header
            class="mx-auto flex max-w-7xl items-center justify-between px-8 py-6"
        >
            <h1 class="text-xl font-bold text-sky-600">OdontoUni</h1>

            <nav class="flex gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded border border-sky-600 px-4 py-2 text-sky-600 hover:bg-sky-50"
                >
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="login()"
                        class="px-4 py-2 text-gray-600 hover:text-sky-600"
                    >
                        Entrar
                    </Link>

                    <Link
                        :href="register()"
                        class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700"
                    >
                        Solicitar acesso
                    </Link>
                </template>
            </nav>
        </header>

        <!-- HERO -->
        <section
            class="mx-auto grid max-w-7xl items-center gap-16 px-8 py-24 lg:grid-cols-2"
        >
            <div>
                <h2 class="mb-6 text-4xl leading-tight font-bold lg:text-5xl">
                    Gestão inteligente para
                    <span class="text-sky-600"
                        >clínicas odontológicas universitárias</span
                    >
                </h2>

                <p class="mb-8 text-lg text-gray-600">
                    Centralize alunos, pacientes, atendimentos e períodos
                    acadêmicos em um único sistema moderno e seguro.
                </p>

                <div class="flex gap-4">
                    <Link
                        :href="register()"
                        class="rounded bg-sky-600 px-6 py-3 font-medium text-white hover:bg-sky-700"
                    >
                        Começar agora
                    </Link>

                    <Link
                        :href="login()"
                        class="rounded border border-gray-300 px-6 py-3 hover:border-sky-600"
                    >
                        Já tenho acesso
                    </Link>
                </div>
            </div>

            <!-- ILUSTRAÇÃO -->
            <div class="relative">
                <div
                    class="absolute inset-0 rounded-3xl bg-sky-100 opacity-60 blur-3xl"
                ></div>
                <img
                    src="/images/odontologia-dashboard.png"
                    alt="Sistema odontológico"
                    class="relative rounded-xl shadow-xl"
                />
            </div>
        </section>

        <!-- BENEFÍCIOS -->
        <section class="bg-gray-50 py-20">
            <div class="mx-auto max-w-7xl px-8">
                <h3 class="mb-12 text-center text-3xl font-bold">
                    Tudo o que sua universidade precisa
                </h3>

                <div class="grid gap-8 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h4 class="mb-2 text-lg font-semibold">
                            Gestão Acadêmica
                        </h4>
                        <p class="text-gray-600">
                            Controle períodos, especialidades e vínculo dos
                            alunos com a clínica.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h4 class="mb-2 text-lg font-semibold">
                            Atendimentos Clínicos
                        </h4>
                        <p class="text-gray-600">
                            Histórico completo de pacientes, procedimentos e
                            supervisões.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h4 class="mb-2 text-lg font-semibold">
                            Seguro e Centralizado
                        </h4>
                        <p class="text-gray-600">
                            Dados protegidos, acessos controlados e tudo em um
                            só lugar.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="py-20">
            <div class="mx-auto max-w-4xl px-8 text-center">
                <h3 class="mb-4 text-3xl font-bold">
                    Leve sua clínica-escola para o próximo nível
                </h3>

                <p class="mb-8 text-gray-600">
                    Um sistema feito sob medida para universidades e cursos de
                    odontologia.
                </p>

                <Link
                    :href="register()"
                    class="inline-block rounded bg-sky-600 px-8 py-4 text-lg text-white hover:bg-sky-700"
                >
                    Solicitar acesso
                </Link>
            </div>
        </section>
    </div>
</template>
