<template>
    <AppLayout>
        <div class="mt-25 flex justify-center">
            <div class="w-full max-w-7xl">
                <div class="overflow-hidden rounded-lg bg-white shadow-lg">
                    <div class="p-6 text-gray-900">
                        <CreateButton
                            label="Novo Funcionário"
                            icon="fas fa-user-plus"
                            @click="openCreateModal"
                        />

                        <EmployeeCreateModal
                            ref="employeeFormRef"
                            @reload-table="reloadTable"
                        />
                        <EmployeeDeleteModal
                            ref="employeeDeleteRef"
                            @reload-table="reloadTable"
                        />
                        <EmployeesTable
                            @edit="openEditModal"
                            @delete="openDeleteModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import CreateButton from '@/components/buttons/CreateButton.vue';
import { EmployeesKey } from '@/keys/employees/employeesKey';
import { SelectedEmployeeKey } from '@/keys/employees/selectedEmployeeKey';
import { PermissionsKey } from '@/keys/permissions/permissionsKey';
import AppLayout from '@/layouts/AppLayout.vue';
import EmployeeCreateModal from '@/pages/employees/EmployeeCreateModal.vue';
import EmployeeDeleteModal from '@/pages/employees/EmployeeDeleteModal.vue';
import EmployeesTable from '@/pages/employees/EmployeesTable.vue';
import axios from 'axios';
import { provide, ref } from 'vue';

interface Address {
    cep?: string;
    address?: string;
    neighborhood?: string;
    number?: string;
    extra_info?: string;
    state?: string;
    city?: string;
}

interface Employee {
    id: number;
    name: string;
    email: string;
    photo?: string | null;
    permission_id: number;
    permission_type: string;
    birth_date?: string;
    cpf?: string;
    address_id?: number;
    address?: Address | null;
}

const props = defineProps<{
    employees: { data: Employee[] };
    permissions: { id: number; type: string }[];
}>();

const employeesData = ref(props.employees);
const permissionsData = ref(props.permissions);

console.log(props);

provide(EmployeesKey, employeesData);
provide(PermissionsKey, permissionsData);

const selectedEmployee = ref<Employee | null>(null);
provide(SelectedEmployeeKey, selectedEmployee);

const employeeFormRef = ref<any>(null);
const employeeDeleteRef = ref<any>(null);

function openEditModal(id: number) {
    selectedEmployee.value =
        employeesData.value.data.find((e) => e.id === id) || null;
    employeeFormRef.value?.open();
}

function openCreateModal() {
    employeeFormRef.value?.open();
}

function openDeleteModal(id: number) {
    selectedEmployee.value =
        employeesData.value.data.find((e) => e.id === id) || null;
    employeeDeleteRef.value?.open();
    console.log(selectedEmployee.value);
}

async function reloadTable() {
    const res = await axios.get(`/employees/table`);
    console.log(res);
    employeesData.value = res.data.employees;
    permissionsData.value = res.data.permissions;
}
</script>
