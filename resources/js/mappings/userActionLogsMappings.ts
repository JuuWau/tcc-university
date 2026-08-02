import { useI18n } from 'vue-i18n';

import {
    formatDateBr,
    formatDateTimeBr,
} from '@/src/utils/formatters';

const actionColors: Record<string, string> = {
    create: 'bg-green-100 text-green-600',
    update: 'bg-blue-100 text-blue-600',
    delete: 'bg-red-100 text-red-600',
};

const dateFields = [
    'date',
    'enrolled_at',
    'created_at',
    'updated_at',
    'deleted_at',
];

const dateTimeFields = [
    'start_at',
    'end_at',
    'scheduled_start_at',
    'scheduled_end_at',
];

export function useUserActionLogs() {
    const { t, te } = useI18n();

    const getActionLabel = (action: string): string => {
        const key = `userActionLogs.actions.${action}`;

        return te(key) ? t(key) : action;
    };

    const getFieldLabel = (field: string): string => {
        const key = `userActionLogs.fields.${field}`;

        return te(key) ? t(key) : field;
    };

    const getActionColor = (action: string): string =>
        actionColors[action] ?? 'bg-gray-100 text-gray-600';

    const formatFieldValue = (
        field: string,
        value: unknown,
    ): string => {
        if (value === null || value === undefined) {
            return '-';
        }

        if (Array.isArray(value)) {
            return value
                .map((item) => formatFieldValue(field, item))
                .join(', ');
        }

        if (typeof value === 'boolean') {
            return value ? t('common.yes') : t('common.no');
        }

        if (typeof value === 'string') {
            if (field === 'status') {
                const key = `userActionLogs.status.${value}`;

                return te(key) ? t(key) : value;
            }

            if (dateFields.includes(field)) {
                return formatDateBr(value);
            }

            if (dateTimeFields.includes(field)) {
                return formatDateTimeBr(value);
            }
        }

        return String(value);
    };

    return {
        getActionLabel,
        getActionColor,
        getFieldLabel,
        formatFieldValue,
    };
}