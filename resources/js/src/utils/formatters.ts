export function formatDateBr(date: string): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString('pt-BR');
}

export function formatTime(time: string): string {
    return time?.slice(0, 5) ?? '';
}

export function formatDateTimeBr(date: string): string {
    if (!date) return '';
    return new Date(date).toLocaleString('pt-BR');
}