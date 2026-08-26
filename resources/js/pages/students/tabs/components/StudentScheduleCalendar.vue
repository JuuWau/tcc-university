<script setup lang="ts">
import type { MonthDay } from '@/types/student/studentSchedule';
import { StudentScheduleContextKey, type StudentScheduleContext } from '@/keys/students/studentScheduleKeys';
import { isPastDate } from '@/src/utils/formatters';
import { inject, computed } from 'vue';

const schedule = inject(StudentScheduleContextKey) as StudentScheduleContext;

if (!schedule) {
    throw new Error('StudentScheduleCalendar must be used inside StudentScheduleTab');
}

const weekDays = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

const visibleMonths = computed(() => schedule.visibleMonths?.value || []);
const selectedDate = computed(() => schedule.selectedDate?.value);

function getDayClass(day: MonthDay) {
    if (selectedDate.value === day.date) {
        return 'bg-sky-600 text-white cursor-pointer hover:bg-sky-700';
    }

    if (isOpenDay(day.date)) {
        if (isPastDate(day.date)) {
            return 'bg-slate-200 text-slate-600 hover:bg-slate-300 cursor-pointer';
        }

        return 'bg-sky-100 text-sky-700 hover:bg-sky-200 cursor-pointer';
    }

    if (schedule.hasEvent(day.date)) {
        return 'bg-white text-gray-700 cursor-not-allowed border border-gray-200';
    }

    return 'bg-gray-100 text-gray-300 cursor-not-allowed';
}

function selectDay(day: MonthDay) {
    schedule.selectDay(day);
}

const openDays = computed(() => schedule.openDays?.value ?? []);

function isOpenDay(date: string) {
    return openDays.value.some(
        (day) => day === date,
    );
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div
            v-for="month in visibleMonths"
            :key="month.month"
            class="rounded-2xl border border-gray-100 bg-gray-50 p-4"
        >
            <div
                class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3"
            >
                <h3 class="text-sm font-semibold capitalize text-gray-800">
                    {{ month.label }}
                </h3>

                <span
                    class="rounded-md bg-white px-2 py-1 text-xs text-gray-500"
                >
                    {{ month.days.filter(day => day.day !== 0).length }} dias
                </span>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                <div
                    v-for="d in weekDays"
                    :key="d"
                    class="pb-2 font-semibold text-gray-400"
                >
                    {{ d }}
                </div>

                <div
                    v-for="day in month.days"
                    :key="day.date"
                    class="flex h-10 items-center justify-center rounded-lg text-sm font-medium transition"
                    :class="
                        day.day === 0
                            ? 'pointer-events-none invisible'
                            : getDayClass(day)
                    "
                    @click="
                        day.day !== 0 &&
                        isOpenDay(day.date) &&
                        selectDay(day)
                    "
                >
                    {{ day.day !== 0 ? day.day : '' }}
                </div>
            </div>
        </div>
    </div>
</template>