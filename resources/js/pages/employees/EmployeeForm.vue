<template>
    <div>
        <slot name="trigger" :open="open"></slot>
        <TransitionRoot :show="isOpen" as="template">
            <Dialog as="div" class="relative z-10" @close="close">
                <TransitionChild
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                    as="template"
                >
                    <div
                        class="bg-opacity-50 fixed inset-0 transition-opacity"
                    ></div>
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-center justify-center p-4 text-center"
                    >
                        <TransitionChild
                            enter="ease-out duration-300"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                            as="template"
                        >
                            <DialogPanel
                                class="w-full max-w-4xl transform overflow-hidden rounded-lg bg-white p-6 text-left align-middle text-gray-700 shadow-xl transition-all"
                            >
                                <DialogTitle
                                    class="text-xl font-semibold text-gray-600"
                                    >Funcionário</DialogTitle
                                >
                                <hr class="mt-5 mb-5 border-gray-200" />

                                <form @submit.prevent="" class="space-y-6">
                                    <div
                                        class="flex flex-col items-start gap-6 md:flex-row"
                                    >
                                        <div
                                            class="flex w-full justify-center md:w-1/4 md:justify-start"
                                        >
                                            <div
                                                class="relative mt-5 h-45 w-45 rounded-md border border-gray-300 shadow-sm"
                                            >
                                                <img
                                                    v-if="form.photoPreview"
                                                    :src="form.photoPreview"
                                                    alt="Foto"
                                                    class="h-full w-full rounded-md object-cover"
                                                />
                                                <label
                                                    for="photo"
                                                    class="bg-opacity-75 hover:bg-opacity-100 absolute bottom-2 left-1/2 -translate-x-1/2 transform cursor-pointer rounded-full bg-white p-2 shadow"
                                                >
                                                    <i
                                                        class="fas fa-camera text-xl text-gray-700"
                                                    ></i>
                                                </label>
                                            </div>
                                            <input
                                                type="file"
                                                id="photo"
                                                class="hidden"
                                                @change="previewPhoto"
                                            />
                                        </div>

                                        <div
                                            class="flex w-full flex-1 flex-col gap-4"
                                        >
                                            <div
                                                class="flex flex-col gap-4 md:flex-row"
                                            >
                                                <div class="w-full md:w-3/4">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                        >Nome</label
                                                    >
                                                    <input
                                                        v-model="form.name"
                                                        type="text"
                                                        class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                        required
                                                    />
                                                </div>
                                                <div class="w-full md:w-1/4">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                        >CPF</label
                                                    >
                                                    <input
                                                        v-model="form.cpf"
                                                        v-mask="
                                                            '###.###.###-##'
                                                        "
                                                        type="text"
                                                        maxlength="14"
                                                        class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                    />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col gap-4 md:flex-row"
                                            >
                                                <div class="w-full md:w-1/2">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                        >E-mail</label
                                                    >
                                                    <input
                                                        v-model="form.email"
                                                        type="email"
                                                        class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                        required
                                                    />
                                                </div>
                                                <div class="w-full md:w-1/2">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                        >Senha</label
                                                    >
                                                    <input
                                                        v-model="form.password"
                                                        type="password"
                                                        class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                    />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col gap-4 md:flex-row"
                                            >
                                                <div class="w-full md:w-1/2">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                        >Data de
                                                        nascimento</label
                                                    >
                                                    <input
                                                        v-model="
                                                            form.birth_date
                                                        "
                                                        type="date"
                                                        class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                        required
                                                    />
                                                </div>
                                                <div class="w-full md:w-1/2">
                                                    <label
                                                        class="block text-sm font-medium text-gray-700"
                                                        >Permissão</label
                                                    >
                                                    <VueSelect
                                                        v-model="
                                                            form.permission_id
                                                        "
                                                        :options="
                                                            permissions.map(
                                                                (p) => ({
                                                                    label: p.type.trim(),
                                                                    value: p.id,
                                                                }),
                                                            )
                                                        "
                                                        placeholder="Selecione uma permissão"
                                                        :classes="{
                                                            control:
                                                                'smaller min-h-full mt-1 block w-full rounded-md border border-gray-300 shadow-sm',
                                                        }"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="mt-6 border-gray-200" />

                                    <div class="flex flex-1 flex-col gap-4">
                                        <div
                                            class="flex flex-col gap-4 md:flex-row"
                                        >
                                            <div class="w-full md:w-1/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >CEP</label
                                                >
                                                <input
                                                    v-model="form.cep"
                                                    type="text"
                                                    maxlength="8"
                                                    class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                    @blur="getAddressByCep"
                                                />
                                            </div>
                                            <div class="w-full md:w-3/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Endereço</label
                                                >
                                                <input
                                                    v-model="form.address"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                />
                                            </div>
                                            <div class="w-full md:w-2/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Bairro</label
                                                >
                                                <input
                                                    v-model="form.neighborhood"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                />
                                            </div>
                                        </div>

                                        <div
                                            class="flex flex-col gap-4 md:flex-row"
                                        >
                                            <div class="w-full md:w-1/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Número</label
                                                >
                                                <input
                                                    v-model="form.number"
                                                    type="text"
                                                    maxlength="20"
                                                    class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                />
                                            </div>
                                            <div class="w-full md:w-1/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Complemento</label
                                                >
                                                <input
                                                    v-model="form.extra_info"
                                                    type="text"
                                                    maxlength="20"
                                                    class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 shadow-sm"
                                                />
                                            </div>
                                            <div class="w-full md:w-2/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Estado</label
                                                >
                                                <VueSelect
                                                    v-model="form.state"
                                                    :options="
                                                        states.map((uf) => ({
                                                            label: uf.nome,
                                                            value: uf.sigla,
                                                        }))
                                                    "
                                                    placeholder="Selecione"
                                                    @update:modelValue="
                                                        loadCities
                                                    "
                                                    :classes="{
                                                        control:
                                                            'smaller min-h-full mt-1 block w-full rounded-md border border-gray-300 shadow-sm',
                                                    }"
                                                />
                                            </div>
                                            <div class="w-full md:w-2/6">
                                                <label
                                                    class="block text-sm font-medium text-gray-700"
                                                    >Cidade</label
                                                >
                                                <VueSelect
                                                    v-model="form.city"
                                                    :options="
                                                        cities.map((m) => ({
                                                            label: m.nome,
                                                            value: m.nome,
                                                        }))
                                                    "
                                                    :disabled="!form.state"
                                                    placeholder="Selecione"
                                                    :classes="{
                                                        control:
                                                            'smaller min-h-full mt-1 block w-full rounded-md border border-gray-300 shadow-sm',
                                                    }"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-6 flex flex-col justify-end gap-2 md:flex-row"
                                    >
                                        <button
                                            type="button"
                                            class="w-full rounded bg-gray-200 px-4 py-2 md:w-auto"
                                            @click="close"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            type="submit"
                                            class="w-full rounded bg-blue-600 px-4 py-2 text-white md:w-auto"
                                            @click="submitForm"
                                        >
                                            Salvar
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>
<style>
.control.smaller {
    min-height: 100% !important;
}
</style>

<script setup lang="ts">
import { ref, inject, reactive, type Ref } from 'vue';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import vSelect from 'vue-select-next';
import { Container } from 'lucide-vue-next';
import axios from 'axios';
import { PermissionsKey } from '@/keys/permissions/permissionsKey';
import { SelectedEmployeeKey } from '@/keys/employees/selectedEmployeeKey';
import { IbgeService, Uf, City } from '@/api/ibge';
import { ViaCep } from '@/api/viacep';
import { toast } from 'vue3-toastify';

interface Address {
    id: number;
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
    permission_type?: string;
    birth_date?: string;
    cpf?: string;
    address?: Address | null;
}

const props = defineProps<{
    permissions?: { id: number; type: string }[];
    employee?: Employee | null;
}>();

const components = { vSelect };

const permissions = inject(PermissionsKey);

const selectedEmployee = inject<Ref<Employee | null>>(SelectedEmployeeKey)!;
console.log(selectedEmployee);

const isOpen = ref(false);

const emit = defineEmits<{
    (e: 'realoadTable'): void;
}>();

const states = ref<Uf[]>([]);
const cities = ref<City[]>([]);

const form = reactive({
    photo: null as File | null,
    photoPreview: selectedEmployee?.value?.photo || null,
    name: selectedEmployee?.value?.name || '',
    email: selectedEmployee?.value?.email || '',
    permission_id: selectedEmployee?.value?.permission_id || '',
    birth_date: selectedEmployee?.value?.birth_date || '',
    password: '',
    cpf: selectedEmployee?.value?.cpf || '',
    cep: selectedEmployee?.value?.address?.cep || '',
    address: selectedEmployee?.value?.address?.address || '',
    neighborhood: selectedEmployee?.value?.address?.neighborhood || '',
    number: selectedEmployee?.value?.address?.number || '',
    extra_info: selectedEmployee?.value?.address?.extra_info || '',
    state: selectedEmployee?.value?.address?.state || '',
    city: selectedEmployee?.value?.address?.city || '',
});

function resetForm() {
    console.log('reset form function');
    form.photo = null;
    form.photoPreview = null;
    form.name = '';
    form.email = '';
    form.permission_id = '';
    form.birth_date = '';
    form.password = '';
    form.cpf = '';
    form.cep = '';
    form.address = '';
    form.neighborhood = '';
    form.number = '';
    form.extra_info = '';
    form.state = '';
    form.city = '';

    console.log('form after reset', form);
}

function open() {
    console.log('open form function', form);
    isOpen.value = true;

    IbgeService.getUfData().then((data) => {
        states.value = data;
    });

    if (selectedEmployee.value) {
        form.name = selectedEmployee.value.name || '';
        form.email = selectedEmployee.value.email || '';
        form.permission_id = selectedEmployee.value.permission_id || '';
        form.birth_date = selectedEmployee.value.birth_date || '';
        form.cpf = selectedEmployee.value.cpf || '';
        form.cep = selectedEmployee.value.address?.cep || '';
        form.address = selectedEmployee.value.address?.address || '';
        form.neighborhood = selectedEmployee.value.address?.neighborhood || '';
        form.number = selectedEmployee.value.address?.number || '';
        form.extra_info = selectedEmployee.value.address?.extra_info || '';
        form.state = selectedEmployee.value.address?.state || '';
        form.city = selectedEmployee.value.address?.city || '';
        form.photoPreview = selectedEmployee.value.photo
            ? `/storage/${selectedEmployee.value.photo}`
            : null;
        if (selectedEmployee.value?.address?.state) {
            IbgeService.getCityData(selectedEmployee.value.address.state).then(
                (data) => {
                    cities.value = data;
                },
            );
        }
    } else {
        resetForm();
        console.log('reset form');
    }
}

function close() {
    isOpen.value = false;
    resetForm();
}

async function loadCities() {
    if (form.state) {
        cities.value = await IbgeService.getCityData(form.state);
        form.city = '';
    }
}

async function getAddressByCep() {
    if (!form.cep) return;
    const viacepService = ViaCep();
    const data = await viacepService.getCepData(form.cep);
    if (!data) return;

    form.address = data.logradouro || '';
    form.neighborhood = data.bairro || '';
    form.state = data.uf || '';

    cities.value = await IbgeService.getCityData(form.state);

    form.city = data.localidade || '';
}

async function submitForm() {
    const formData = new FormData();
    for (const key in form) {
        const k = key as keyof typeof form;
        let value = form[k];

        if (value !== null && value !== '') {
            formData.append(k, value as any);
        }
    }

    try {
        let res;
        console.log('prop employee', selectedEmployee.value);
        if (selectedEmployee.value?.id) {
            formData.append('_method', 'PUT');
            res = await axios.post(
                `/employees/${selectedEmployee.value.id}`,
                formData,
                {
                    headers: { 'Content-Type': 'multipart/form-data' },
                },
            );
            toast.success(res.data.message);
            emit('reloadTable');
        } else {
            res = await axios.post('/employees', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            toast.success(res.data.message);
            emit('reloadTable');
        }

        close();
    } catch (error: any) {
        console.error(error.response?.data || error);
    }
}

function previewPhoto(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.photo = file;
        form.photoPreview = URL.createObjectURL(file);
    }
}

defineExpose({ open, close });
</script>
