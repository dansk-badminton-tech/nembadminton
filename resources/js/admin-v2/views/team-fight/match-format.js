// Match Format per DH tier (DH-reglementet §37). Exact tier names only:
// variants such as "2. division - slutspil" and Kreds tiers get no suggestion.
const MATCH_FORMAT_BY_TIER = Object.freeze({
    'Badmintonligaen': 9,
    '1. division': 9,
    '2. division': 13,
    '3. division': 13,
    'Danmarksserien': 13
});

// Round 12 qualifies 2. division teams for 1. division and is played as 1. division (§27 stk. 2).
const QUALIFICATION_ROUND = 12;

export function suggestMatchFormat(tierName, roundNumber) {
    const tier = typeof tierName === 'string' ? tierName.trim() : '';
    if (!Object.prototype.hasOwnProperty.call(MATCH_FORMAT_BY_TIER, tier)) {
        return null;
    }
    if (tier === '2. division' && roundNumber === QUALIFICATION_ROUND) {
        return 9;
    }
    return MATCH_FORMAT_BY_TIER[tier];
}

// The add-Squad form's Match Format: `fromTier` names the tier it was preselected from,
// or is null when the coach picked it or the tier is gone.
export function applyTier(current, tierName, roundNumber) {
    const suggestion = suggestMatchFormat(tierName, roundNumber);
    if (suggestion === null) {
        return {matchCount: current.matchCount, fromTier: null};
    }
    return {matchCount: suggestion, fromTier: tierName.trim()};
}

export function pickMatchFormat(current, matchCount) {
    return {
        matchCount,
        fromTier: matchCount === current.matchCount ? current.fromTier : null
    };
}
