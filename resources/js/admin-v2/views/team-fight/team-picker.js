export function teamTierLabel(team) {
    return team?.tier?.tierName || team?.customTierName || '';
}

export function buildTeamPickerOptions(teams, usedTeamIds) {
    const usedIds = new Set((usedTeamIds || []).map(String));

    return (teams || []).map((team) => ({
        id: team.id,
        label: team.name,
        details: [teamTierLabel(team), team.groupName].filter(Boolean).join(' · '),
        added: usedIds.has(String(team.id)),
        team
    }));
}

// Unnamed squads are listed as "Hold N" in the round, so the button says the same.
export function squadSubmitLabel(name, squadNumber) {
    const trimmedName = (name || '').trim();
    return trimmedName === ''
        ? `Tilføj Hold ${squadNumber} uden navn`
        : `Tilføj ${trimmedName}`;
}
