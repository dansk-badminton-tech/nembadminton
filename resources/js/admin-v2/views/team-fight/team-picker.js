export function teamTierLabel(team) {
    return team?.tier?.tierName || team?.customTierName || '';
}

export function describeTeam(team) {
    return [team.name, teamTierLabel(team), team.groupName]
        .filter(Boolean)
        .join(' · ');
}

export function buildTeamPickerOptions(teams, usedTeamIds) {
    const usedIds = new Set((usedTeamIds || []).map(String));

    return (teams || []).map((team) => ({
        id: team.id,
        label: team.name,
        tierLabel: teamTierLabel(team),
        added: usedIds.has(String(team.id)),
        team
    }));
}
