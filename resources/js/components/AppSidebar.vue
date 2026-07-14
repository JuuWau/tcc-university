<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { initialPage } from '@/routes';
import patients from '@/routes/patients';
import periods from '@/routes/periods';
import procedures from '@/routes/procedures';
import { index } from '@/routes/specialties';
import students from '@/routes/students';
import users from '@/routes/users';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BarChart, Building2, Calendar, ClipboardList, GraduationCap, LayoutGrid, ListPlus, Stethoscope, User, Users, Timer, CalendarPlus, CalendarCog } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import schedules from '@/routes/schedules';
import clinics from '@/routes/clinics';

const mainNavItems: NavItem[] = [
    {
        title: 'Bem-vindo',
        href: initialPage().url,
        icon: LayoutGrid,
    },
    {
        title: 'Cadastros',
        icon: ListPlus,
        children: [
            {
                title: 'Especialidades',
                href: index(),
                icon: Stethoscope,
            },
            {
                title: 'Períodos',
                href: periods.index(),
                icon: Timer,
            },
            {
                title: 'Clínicas',
                href: clinics.index(),
                icon: Building2,
            },
            {
                title: 'Procedimentos',
                href: procedures.index(),
                icon: ClipboardList,
            },
            {
                title: 'Estudantes',
                href: students.index(),
                icon: GraduationCap,
            },
            {
                title: 'Pacientes',
                href: patients.index(),
                icon: User,
            },
            {
                title: 'Usuários',
                href: users.index(),
                icon: Users,
            },
        ],
    },
    {
        title: 'Agenda',
        icon: Calendar,
        children: [
            {
                title: 'Abrir agenda',
                href: schedules.openSchedule(),
                icon: CalendarPlus,
            },
            {
                title: 'Gerenciar clínicas abertas',
                href: '/schedules/open-clinics',
                icon: CalendarCog,
            },
            {
                title: 'Clínicas abertas',
                href: '/schedule-enrollment/open-clinics',
            },
            {
                title: 'Confirmar agendamentos',
                href: '/confirm-appointment/confirm-appointment',
            },
            {
                title: 'Chamada de alunos',
                href: '/schedule-attendance/schedule-attendance',
            },
        ],
    },
    {
        title: 'Relatórios',
        icon: BarChart,
        children: [
            {
                title: 'Relatórios de estudantes',
                href: '/reports/students',
            },
            {
                title: 'Relatórios de agendamentos',
                href: '/reports/appointments',
            },
            {
                title: 'Relatórios de clínicas por aluno',
                href: '/reports/clinics-by-student',
            },
            {
                title: 'Relatórios de pacientes',
                href: '/reports/patients',
            },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="initialPage().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
