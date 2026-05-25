export function normalizeDateToDay(date) {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        return null;
    }

    return new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate()
    );
}

export function resolveSeasonIdByDate(date) {
    const normalizedDate = normalizeDateToDay(date);
    if (normalizedDate === null) {
        return null;
    }

    if (normalizedDate.getMonth() >= 6) {
        return normalizedDate.getFullYear();
    }

    return normalizedDate.getFullYear() - 1;
}

export function resolveDateByOffset(baseDate, offset) {
    const normalizedBaseDate = normalizeDateToDay(baseDate);
    if (normalizedBaseDate === null) {
        return null;
    }

    const resolvedDate = new Date(normalizedBaseDate);
    resolvedDate.setDate(resolvedDate.getDate() + offset);
    return resolvedDate;
}

export function isSameDay(firstDate, secondDate) {
    const normalizedFirstDate = normalizeDateToDay(firstDate);
    const normalizedSecondDate = normalizeDateToDay(secondDate);

    if (normalizedFirstDate === null || normalizedSecondDate === null) {
        return false;
    }

    return normalizedFirstDate.getFullYear() === normalizedSecondDate.getFullYear()
        && normalizedFirstDate.getMonth() === normalizedSecondDate.getMonth()
        && normalizedFirstDate.getDate() === normalizedSecondDate.getDate();
}
