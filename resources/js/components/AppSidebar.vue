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
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BarChart, Building2, Calendar, ClipboardList, GraduationCap, LayoutGrid, ListPlus, Stethoscope, User, Users, Timer, CalendarPlus, CalendarCog, CalendarCheck, Hospital, LogIn, Notebook, FileUser, BookUser, UsersRound } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();

const permissions = computed(
    () => page.props.auth.permissions ?? []
);

const can = (permission: string) => {
    return permissions.value.includes(permission);
};

const mainNavItems: NavItem[] = [
    {
        title: 'Bem-vindo',
        href: '/initialPage',
        icon: LayoutGrid,
    },
    {
        title: 'Cadastros',
        icon: ListPlus,
        children: [
            {
                title: 'Especialidades',
                href: '/specialties',
                icon: Stethoscope,
                permission: 'specialties.view',
            },
            {
                title: 'Períodos',
                href: '/periods',
                icon: Timer,
                permission: 'periods.view',
            },
            {
                title: 'Clínicas',
                href: '/clinics',
                icon: Building2,
                permission: 'clinics.view',
            },
            {
                title: 'Procedimentos',
                href: '/procedures',
                icon: ClipboardList,
                permission: 'procedures.view',
            }
        ],
    },
    {
        title: 'Gestão de pessoas',
        icon: UsersRound,
        children: [
            {
                title: 'Estudantes',
                href: '/students',
                icon: GraduationCap,
                permission: 'students.view',
            },
            {
                title: 'Pacientes',
                href: '/patients',
                icon: User,
                permission: 'patients.view',
            },
            {
                title: 'Usuários',
                href: '/users',
                icon: Users,
                permission: 'users.view',
            },
        ],
    },
    {
        title: 'Agenda',
        icon: Calendar,
        children: [
            {
                title: 'Abrir dias',
                href: '/schedules/open-schedule',
                icon: CalendarPlus,
                permission: 'open-schedule.open',
            },
            {
                title: 'Agenda das clínicas',
                href: '/schedules/open-clinics',
                icon: CalendarCog,
                permission: 'open-schedule-management.view'
            },
            {
                title: 'Clínicas abertas',
                href: '/schedule-enrollment/open-clinics',
                icon: LogIn,
                permission: 'open-schedule-management-student.view',
            },
        ],
    },
    {
        title: 'Gerenciar clínicas',
        href: '/clinics-management/',
        icon: Hospital,
        permission: 'clinics-management.view'
    },
    {
        title: 'Confirmar agendamentos',
        href: '/appointments-confirmation/',
        icon: CalendarCheck,
        permission: 'appointments-confirmation.view'
    },
    {
        title: 'Controle de presença',
        href: '/attendance/clinics',
        icon: ClipboardList,
        permission: 'attendance.view',
    },
    {
        title: 'Relatórios',
        icon: BarChart,
        children: [
            {
                title: 'Relatórios de estudantes',
                href: '/reports/students',
                icon: FileUser,
                permission: 'students-reports.view'
            },
            {
                title: 'Relatórios de agendamentos',
                href: '/reports/appointments',
                icon: Notebook,
                permission: 'appointments-reports.view'
            },
            {
                title: 'Relatórios de clínicas por aluno',
                href: '/reports/clinics-by-student',
                permission: 'clinics-reports.view'
            },
            {
                title: 'Relatórios de pacientes',
                href: '/reports/patients',
                icon: BookUser,
                permission: 'patients-reports.view'
            },
        ],
    },
];

const filteredNavItems = computed(() => {
    return mainNavItems
        .map((item) => {
            if (!item.children) {
                return item;
            }

            return {
                ...item,
                children: item.children.filter((child) => {
                    return !child.permission || can(child.permission);
                }),
            };
        })
        .filter((item) => {
            if (item.permission && !can(item.permission)) {
                return false;
            }

            if (item.children) {
                return item.children.length > 0;
            }

            return true;
        });
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
