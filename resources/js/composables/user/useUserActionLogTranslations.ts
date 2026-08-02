import { useI18n } from 'vue-i18n';
import { formatDateBr, formatDateTimeBr,} from '@/src/utils/formatters';

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
const hiddenFields = new Set([
    'university_id',
    'clinic_id',
    'specialty_id',
    'slot',
    'period_id'
]);

export function useUserActionLogTranslations() {
    const { t, te } = useI18n();

    const getActionLabel = (action: string) => {
        const key = `userActionLogs.actions.${action}`;

        return te(key) ? t(key) : action;
    };

    const getFieldLabel = (field: string) => {
        const key = `userActionLogs.fields.${field}`;

        return te(key) ? t(key) : field;
    };

    const getActionColor = (action: string) =>
        actionColors[action] ?? 'bg-gray-100 text-gray-600';

    const formatFieldValue = (
        field: string,
        value: unknown,
    ): string => {
        if (value == null) {
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

        if (field === 'status' && typeof value === 'string') {
            const key = `userActionLogs.status.${value}`;

            return te(key) ? t(key) : value;
        }

        if (typeof value === 'string') {
            if (dateFields.includes(field)) {
                return formatDateBr(value);
            }

            if (dateTimeFields.includes(field)) {
                return formatDateTimeBr(value);
            }
        }

        return String(value);
    };

    function shouldShowField(field: string) {
        return !hiddenFields.has(field);
    }

    function visibleChanges(changes: Record<string, { old: unknown; new: unknown }>,) 
    {
        return Object.entries(changes).filter(([field]) =>
            shouldShowField(field),
        );
    }

    return {
        getActionLabel,
        getActionColor,
        getFieldLabel,
        formatFieldValue,
        shouldShowField,
        visibleChanges
    };
}