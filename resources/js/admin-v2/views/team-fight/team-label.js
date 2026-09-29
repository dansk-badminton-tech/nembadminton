export function teamTierLabel(team) {
    return team?.tier?.tierName || team?.customTierName || '';
}

export function teamLabel(team) {
    if (!team) {
        return '';
    }
    return [team.name, teamTierLabel(team), team.groupName]
        .filter((part) => part)
        .join(' · ');
}
