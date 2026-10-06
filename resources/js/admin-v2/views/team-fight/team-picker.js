import {teamTierLabel} from './team-label.js';

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
    const squad = trimmedName === '' ? `Hold ${squadNumber} uden navn` : trimmedName;
    return `Tilføj "${squad}" til holdopstillingen`;
}
