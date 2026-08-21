<script setup lang="ts">
import { computed, inject, provide, ref } from 'vue';
import { PatientScheduleBookingContextKey, PatientScheduleCreateModalKey, PatientScheduleViewModalKey } from '@/keys/patients/patientScheduleBookingKeys';
import PatientScheduleBookingDayEvents from './PatientScheduleBookingDayEvents.vue';
import { PatientScheduleBookingAppointment, PatientScheduleCreateData } from '@/types/patient/patientScheduleBooking.js';
import PatientScheduleBookingAppointmentViewModal from './PatientScheduleBookingAppointmentViewModal.vue';
import PatientScheduleBookingAppointmentCreateModal from './PatientScheduleBookingAppointmentCreateModal.vue';

const booking = inject(PatientScheduleBookingContextKey);

if (!booking) {
    throw new Error(
        'PatientScheduleBookingCalendar deve ser usado dentro de PatientScheduleBooking.',
    );
}

const viewModal = {
    isOpen: ref(false),
    appointment: ref<PatientScheduleBookingAppointment | null>(null),
    initialData: ref({
        date: '',
        start_time: '',
        end_time: '',
    }),
    patientOptions: booking.patientOptions,
    procedureOptions: booking.procedureOptions,
};

const createModal = {
    isOpen: ref(false),
    initialData: ref<PatientScheduleCreateData>({
        schedule_enrollment_id: null,
        date: '',
        start_time: '',
        end_time: '',
        allow_procedure_booking: false,
        patient_id: booking.patientId,
        patient: booking.patientName,
        status: 'scheduled',
        notes: '',
    }),

    procedureOptions: booking.procedureOptions,
};

provide(PatientScheduleViewModalKey, viewModal)
provide(PatientScheduleCreateModalKey, createModal,);

const currentMonth = booking.currentMonth;
const monthDays = booking.monthDays;
const monthLabel = booking.monthLabel;

const selectedDate = booking.selectedDate;
const availableDays = booking.availableDays;

function isOpenDay(date: string) {
    return availableDays.value.some(
        (day) => day.date === date,
    );
}

function getDayClass(date: string) {
    if (selectedDate.value === date) {
        return 'bg-sky-600 text-white cursor-pointer hover:bg-sky-700';
    }

    if (isOpenDay(date)) {
        return 'bg-sky-100 text-sky-700 cursor-pointer hover:bg-sky-200';
    }

    return 'bg-gray-100 text-gray-300 cursor-not-allowed';
}

function selectDay(date: string) {
    if (!isOpenDay(date)) {
        return;
    }

    booking.selectDate(date);
}

function isToday(date: string) {
    const today = new Date();

    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');

    return date === `${year}-${month}-${day}`;
}

function previousMonth() {
    booking.previousMonth();
}

function nextMonth() {
    booking.nextMonth();
}

function goToToday() {
    const today = new Date();

    currentMonth.value = new Date(
        today.getFullYear(),
        today.getMonth(),
        1,
    );

    booking.loadAvailableDays();
}

function openViewModal(appointment: PatientScheduleBookingAppointment,) 
{
    viewModal.appointment.value = appointment;
    viewModal.isOpen.value = true;
}

function openCreateModal(data: PatientScheduleCreateData) {
    createModal.initialData.value = data;
    createModal.isOpen.value = true;
}
</script>

<template>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[420px_1fr]">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div
                class="mb-5 flex items-center justify-between border-b border-gray-200 pb-4"
            >
                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                    @click="previousMonth"
                >
                    ←
                </button>

                <div class="text-center">
                    <h3
                        class="text-base font-semibold capitalize text-gray-900"
                    >
                        {{ monthLabel }}
                    </h3>

                    <button
                        type="button"
                        class="mt-1 text-xs text-sky-600 hover:text-sky-700"
                        @click="goToToday"
                    >
                        Hoje
                    </button>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                    @click="nextMonth"
                >
                    →
                </button>
            </div>

            <div
                class="mb-2 grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-400"
            >
                <div
                    v-for="weekDay in ['D', 'S', 'T', 'Q', 'Q', 'S', 'S']"
                    :key="weekDay"
                >
                    {{ weekDay }}
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <div
                    v-for="(day, index) in monthDays"
                    :key="day.date || `empty-${index}`"
                    class="flex h-11 items-center justify-center"
                >
                    <button
                        v-if="day.day !== 0"
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-medium transition"
                        :class="[
                            getDayClass(day.date),

                            isToday(day.date) &&
                            !isOpenDay(day.date) &&
                            selectedDate !== day.date
                                ? 'border border-sky-400'
                                : '',
                        ]"
                        @click="selectDay(day.date)"
                    >
                        {{ day.day }}
                    </button>
                </div>
            </div>

            <div class="mt-5 border-t border-gray-100 pt-4">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span
                        class="h-3 w-3 rounded-full bg-sky-100"
                    />

                    <span>
                        Dia disponível
                    </span>
                </div>
            </div>
        </div>

        <PatientScheduleBookingDayEvents 
            @view="openViewModal"
            @create="openCreateModal"
        />

        <PatientScheduleBookingAppointmentViewModal />

        <PatientScheduleBookingAppointmentCreateModal />
    </div>
</template>