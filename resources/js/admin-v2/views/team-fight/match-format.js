// Match Format per DH tier (DH-reglementet §37). Exact tier names only:
// variants such as "2. division - slutspil" and kreds tiers get no suggestion.
const MATCH_FORMAT_BY_TIER = Object.freeze({
    'Badmintonligaen': 9,
    '1. division': 9,
    '2. division': 13,
    '3. division': 13,
    'Danmarksserien': 13,
});

// Round 12 of 2. division is the qualification for 1. division, played as 9 matches (§27 stk. 2).
const QUALIFICATION_ROUND = 12;

export function suggestMatchFormat(tierName, round) {
    const tier = typeof tierName === 'string' ? tierName.trim() : '';
    if (!Object.prototype.hasOwnProperty.call(MATCH_FORMAT_BY_TIER, tier)) {
        return null;
    }
    if (tier === '2. division' && round === QUALIFICATION_ROUND) {
        return 9;
    }
    return MATCH_FORMAT_BY_TIER[tier];
}

// The add-Squad form's Match Format after the tier changes. A suggestion replaces any
// earlier pick; without one the format stays and the hint goes.
export function applyTierToMatchFormat(form, tierName, round) {
    const suggestion = suggestMatchFormat(tierName, round);
    if (suggestion === null) {
        return {matchCount: form.matchCount, suggestedTier: null};
    }
    return {matchCount: suggestion, suggestedTier: tierName.trim()};
}
