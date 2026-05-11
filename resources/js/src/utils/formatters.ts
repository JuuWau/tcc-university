export function formatDateBr(date: string): string {
    if (!date) return '';
    const [year, month, day] = date.split('-');
    return `${day}/${month}/${year}`;
}

export function formatTime(time: string): string {
    return time?.slice(0, 5) ?? '';
}

export function formatDateTimeBr(date: string): string {
    if (!date) return '';
    return new Date(date).toLocaleString('pt-BR');
}