// The Check Status of every Check in the team-round editor (GLOSSARY.md: Check, Check Status). The only place where the
// server's per-player outcomes (SATISFIED, REQUIREMENT_NOT_MET, NOT_APPLICABLE, CANNOT_VERIFY, *IncompleteReason) become
// Check Statuses. It owns Not Applicable from the Lineup, Switched Off, Waiting, each Check's severity and the precedence:
// Not Applicable > Switched Off > Waiting, then a Check that ran takes its worst outcome (Fejl > Advarsel >
// Kan ikke kontrolleres > OK). Framework-free, so node:test can load it.

import {reserveFindingMessages} from '../reserves.js'
import {hasPaperSquad, paperSquadPlayerMessage} from '../paper-squads.js'
import {playoffParticipationMessage} from '../playoff-participation.js'

export const CHECK_STATUS = {
    PASSED: 'passed',
    FAILED: 'failed',
    WARNING: 'warning',
    CANNOT_VERIFY: 'cannot-verify',
    WAITING: 'waiting',
    SWITCHED_OFF: 'switched-off',
    NOT_APPLICABLE: 'not-applicable'
}

export const STATUS_LABEL = {
    [CHECK_STATUS.PASSED]: 'OK',
    [CHECK_STATUS.FAILED]: 'Fejl',
    [CHECK_STATUS.WARNING]: 'Advarsel',
    [CHECK_STATUS.CANNOT_VERIFY]: 'Kan ikke kontrolleres',
    [CHECK_STATUS.WAITING]: 'Venter',
    [CHECK_STATUS.SWITCHED_OFF]: 'Slået fra',
    [CHECK_STATUS.NOT_APPLICABLE]: 'Gælder ikke'
}

// A Check that ran takes its worst outcome.
const WORST_FIRST = [CHECK_STATUS.FAILED, CHECK_STATUS.WARNING, CHECK_STATUS.CANNOT_VERIFY]

const RULES = [
    {
        key: 'complete',
        name: 'Hold fuldendt',
        rule: null,
        description: 'Alle hold har en spiller på hver plads.',
        switchable: true,
        response: 'basicSquads',
        run: basicSquads => ({
            findings: basicSquads
                .filter(squad => !isFull(squad))
                .map(squad => ({outcome: 'broken', message: `Hold ${squad.index + 1} mangler spillere.`}))
        })
    },
    {
        key: 'level',
        name: 'Niveau',
        rule: '§38 stk. 4',
        description: 'Ingen spiller står på et lavere hold end en spiller med for få point i forhold til vedkommende.',
        waitsForFullSquads: true,
        response: 'crossSquads',
        // Youth Players are not validated on points (§38 stk. 5); the team table marks them green instead.
        run: players => ({
            findings: players.filter(player => !player.isYouthPlayer).map(player => playerFinding(player, 'broken',
                `${player.name} spiller på et for lavt rangeret hold i forhold til ${belowNames(player)}.`))
        })
    },
    {
        key: 'category',
        name: 'Kategori',
        rule: '§38 stk. 2–3',
        description: 'Ingen spiller spiller for højt i sin kategori på holdet.',
        waitsForFullSquads: true,
        response: 'squads',
        // As Niveau, and a player with a youth partner is left out too.
        run: players => ({
            findings: players.filter(player => !player.isYouthPlayer && !player.hasYouthPlayerPartner).map(player => playerFinding(player, 'broken',
                `${player.name} spiller for højt i sin kategori (${player.category}) i forhold til ${belowNames(player)}.`))
        })
    },
    {
        key: 'reserves',
        name: 'Reserver',
        rule: '§43',
        description: 'En reserve har ikke flere point end spilleren, vedkommende erstatter.',
        severity: CHECK_STATUS.WARNING,
        notApplicable: ({teamRound}) => squadsOf(teamRound).some(squad => squad.reserves?.length) ? null : 'Opstillingen har ingen reserver.',
        response: 'reserves',
        run: findings => ({
            findings: findings.map(finding => playerFinding({squadIndex: finding.squadIndex, ...finding.reserve}, 'broken',
                reserveFindingMessages([finding])[0]))
        })
    },
    {
        key: 'paperSquads',
        name: 'Papirhold',
        rule: '§38 stk. 7',
        description: 'Spillerne på papirholdet har spillet i kategorien og på samme eller et lavere hold før.',
        notApplicable: ({teamRound}) => hasPaperSquad(squadsOf(teamRound)) ? null : 'Opstillingen har intet papirhold.',
        response: 'paperSquads',
        run: ({missingRound, players}) => {
            if (missingRound) return {findings: [], reason: GUIDANCE.MISSING_ROUND}
            const findings = players.map(player => playerFinding(player,
                paperSquadCountMayBeMet(player) ? 'unknown' : 'broken',
                paperSquadPlayerMessage(player),
                {uncountedRounds: player.uncountedRounds}))
            return {findings, reason: findings.some(finding => finding.outcome === 'unknown') ? LINK_EARLIER_ROUNDS : null}
        }
    },
    {
        key: 'finalRound',
        name: 'Tidligere hold',
        rule: '§38 stk. 8',
        description: 'Spillerne har stået på samme eller et lavere hold tidligere i sæsonen.',
        switchable: true,
        notApplicable: ({teamRound}) => notCovered(teamRound, coversFinalRound,
            'Gælder kun runde 11–12 i 1.–3. division og Danmarksserien og runde 12 i Badmintonligaen.'),
        response: 'finalRound',
        run: players => historyResult(players, player => player.status === 'REQUIREMENT_NOT_MET'
            ? `Hold ${player.squadIndex + 1}: ${player.name} har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8).`
            : `Hold ${player.squadIndex + 1}: ${player.name} – spilleberettigelse kan ikke verificeres fuldt ud (§38 stk. 8).`)
    },
    {
        key: 'qualification',
        name: 'Kvalifikation',
        rule: '§27 stk. 5',
        description: 'Spillerne opfylder rundenummerkravene til kvalifikationen.',
        switchable: true,
        severity: CHECK_STATUS.WARNING,
        notApplicable: ({teamRound}) => notCovered(teamRound, coversQualification,
            'Gælder kun runde 12 i 2.–3. division og Danmarksserien.'),
        response: 'qualification',
        run: players => historyResult(players, player => player.status === 'REQUIREMENT_NOT_MET'
            ? `Hold ${player.squadIndex + 1}: ${player.name} opfylder ikke de kontrollerede rundenummerkrav (§27 stk. 5); Nembadminton vurderer ikke kalenderårsalternativet.`
            : `Hold ${player.squadIndex + 1}: ${player.name} – kvalifikationskravet kan ikke verificeres fuldt ud (§27 stk. 5).`)
    },
    {
        key: 'playoff',
        name: 'Slutspilskrav',
        rule: '§32 stk. 1',
        description: 'Spillerne har spillet nok kampe for klubben til at spille slutspil.',
        notApplicable: ({teamRound}) => notCovered(teamRound, coversPlayoff,
            'Gælder kun slutspillet i 1. division (fra runde 8) og Badmintonligaen (fra runde 10).'),
        response: 'playoff',
        run: players => historyResult(players, playoffParticipationMessage)
    },
    {
        key: 'tooYoung',
        name: 'For ung',
        rule: '§31',
        perPlayer: true,
        description: 'Spillerne fylder 15 år senest 31.12. i det år, sæsonen starter.',
        response: 'tooYoung',
        run: (players, {teamRound}) => ({
            findings: players.map(player => playerFinding(player, 'broken', teamRound?.seasonStartYear
                ? `${player.name} er ikke fyldt 15 år senest 31.12.${teamRound.seasonStartYear} (§31 stk. 1).`
                : `${player.name} er ikke fyldt 15 år senest 31.12. i det år, sæsonen starter (§31 stk. 1).`))
        })
    }
]

// What to fill in for each *IncompleteReason of the server's per-player outcomes.
const GUIDANCE = {
    MISSING_ROUND: 'Udfyld Runde nr. i holdrundens indstillinger.',
    UNKNOWN_TIER: 'Angiv holdets række under Rediger holdet. Er et hold tilknyttet, skal rækken rettes på sæsonens hold.',
    MISSING_TEAM: 'Tilknyt sæsonens hold via Rediger holdet → Tilknyt hold i denne holdrunde.',
    MISSING_SEASON: 'Holdrunden mangler sæson. Kontakt support for at få den rettet.',
    MISSING_GAME_DATE: 'Angiv datoen i holdrundens indstillinger, så tidligere holdrunder kan findes.',
    UNKNOWN_HISTORY_TIER: 'Angiv rækken på spillerens hold i de tidligere holdrunder herunder.',
    MISSING_HISTORY_DATE: 'Angiv spilledatoen i de tidligere holdrunder herunder.'
}

const CANNOT_DECIDE_FROM_HISTORY = 'Spillernes tidligere holdrunder er ikke nok til at afgøre kravet.'

// Per-player outcomes of the history Checks: any REQUIREMENT_NOT_MET breaks the rule, otherwise any CANNOT_VERIFY makes
// the Check Kan ikke kontrolleres. Other outcomes (SATISFIED, NOT_APPLICABLE) are no finding.
function historyResult(players, message) {
    const findings = players
        .filter(player => player.status === 'REQUIREMENT_NOT_MET' || player.status === 'CANNOT_VERIFY')
        .map(player => playerFinding(player, player.status === 'REQUIREMENT_NOT_MET' ? 'broken' : 'unknown', message(player), {
            guidance: playerGuidance(player),
            uncountedRounds: player.uncountedRounds ?? []
        }))
    const unknown = findings.filter(finding => finding.outcome === 'unknown')
    if (!unknown.length) return {findings}
    const guidance = [...new Set(unknown.flatMap(finding => finding.guidance))]
    return {findings, reason: guidance.join(' ') || CANNOT_DECIDE_FROM_HISTORY}
}

function playerGuidance(player) {
    const guidance = (player.incompleteReasons ?? [])
        .map(reason => reason === 'INCOMPARABLE_HISTORY' ? LINK_EARLIER_ROUNDS : GUIDANCE[reason])
        .filter(Boolean)
    if (player.uncountedRounds?.length) guidance.push(LINK_EARLIER_ROUNDS)
    return [...new Set(guidance)]
}

function roundMissing(teamRound) {
    return teamRound?.round === null || teamRound?.round === undefined || teamRound?.round === ''
}

// Today's round and tier rules decide Not Applicable. A missing round decides nothing: the Check applies when it would
// cover the Lineup in some round, and the server answers Kan ikke kontrolleres.
function notCovered(teamRound, covers, reason) {
    const squads = squadsOf(teamRound)
    if (squads.length && squads.every(squad => squad.isPaperSquad)) return 'Opstillingen har kun papirhold.'
    const covered = roundMissing(teamRound) ? covers({...teamRound, round: 12}) : covers(teamRound)
    return covered ? null : reason
}

// Round 11–12 in 1.–3. division and Danmarksserien, round 12 in Badmintonligaen, on a non-Paper Squad (§38 stk. 8). An
// unknown tier is covered, so the server can answer Kan ikke kontrolleres.
function coversFinalRound(teamRound) {
    const round = Number(teamRound.round)
    if (round !== 11 && round !== 12) return false
    return squadsOf(teamRound).some(squad => {
        if (squad.isPaperSquad) return false
        const tier = (squad.effectiveTier ?? '').trim()
        if (!tier) return true
        if (/^badmintonligaen\b/i.test(tier)) return round === 12
        return /^(?:[1-3]\.\s*division|danmarksserien)\b/i.test(tier)
    })
}

// The first playoff round of each tier §32 stk. 1 covers (§9 stk. 1).
const FIRST_PLAYOFF_ROUND = {liga: 10, division1: 8}

// From the first playoff round in Badmintonligaen and 1. division, on a non-Paper Squad. An unknown tier is covered
// from the earliest of them.
function coversPlayoff(teamRound) {
    const round = Number(teamRound.round)
    if (!Number.isInteger(round)) return false
    return squadsOf(teamRound).some(squad => {
        if (squad.isPaperSquad) return false
        const tier = (squad.effectiveTier ?? '').trim()
        if (!tier) return round >= FIRST_PLAYOFF_ROUND.division1
        if (/^badmintonligaen\b/i.test(tier)) return round >= FIRST_PLAYOFF_ROUND.liga
        if (/^1\.\s*division\b/i.test(tier)) return round >= FIRST_PLAYOFF_ROUND.division1
        return false
    })
}

// Round 12 (or no round) on a non-Paper Squad outside the tiers §27 stk. 5 leaves out. An unknown tier is covered, so
// the server can answer Kan ikke kontrolleres.
function coversQualification(teamRound) {
    if (Number(teamRound.round) !== 12) return false
    return squadsOf(teamRound).some(squad => {
        if (squad.isPaperSquad) return false
        const tier = (squad.effectiveTier ?? '').trim()
        return !/^(?:badmintonligaen\b|1\.\s*division\b|(?:\d+\.\s*)?serie\b|(?:københavns|sjællands|kreds)serien\b|lf-serien\b)/i.test(tier)
    })
}

const LINK_EARLIER_ROUNDS = 'Tilknyt holdet i de tidligere holdrunder, der står under spillerne.'

function squadsOf(teamRound) {
    return teamRound?.squads ?? []
}

// Only the match count is short, and the rounds without the Team linked could make up for it.
function paperSquadCountMayBeMet({unplayedCategories, matchCount, requiredMatchCount, uncountedRounds}) {
    return unplayedCategories.length === 0 && matchCount + uncountedRounds.length >= requiredMatchCount
}

// What the panel shows about each Check, in the panel's order. severity: the status a broken rule gives (Fejl or Advarsel).
// requests: the responses its status waits on, so the panel can tell loading from a failed request while status is null.
export const CHECKS = RULES.map(({key, name, rule, description, severity, switchable, perPlayer, waitsForFullSquads, response}) => ({
    key, name, rule, description,
    severity: severity ?? CHECK_STATUS.FAILED,
    switchable: switchable === true,
    perPlayer: perPlayer === true,
    requests: [...new Set([...(waitsForFullSquads ? ['basicSquads'] : []), response])]
}))

const WAITING_FOR_FULL_SQUADS = 'Venter på, at alle hold er fuldt besat.'

// A Squad from validateBasicSquads with a player on every place.
function isFull(squad) {
    return squad.spotsFulfilled === true && squad.missingPlayerInCategory !== true
}

function playerFinding({squadIndex, refId, name}, outcome, message, extra = {}) {
    return {...(squadIndex === undefined ? {} : {squadIndex}), refId, name, outcome, message, ...extra}
}

// The players below, each named once.
function belowNames(player) {
    const seen = new Set()
    return player.belowPlayer.filter(below => {
        const identity = below.refId ?? below.name
        if (seen.has(identity)) return false
        seen.add(identity)
        return true
    }).map(below => below.name).join(', ')
}

function result(status, reason = null, findings = []) {
    return {status, reason, findings}
}

// A ran Check's findings carry an outcome: 'broken' (the rule is broken: Fejl or Advarsel by severity) or
// 'unknown' (Kan ikke kontrolleres). The worst outcome wins; broken findings are listed first.
function ranResult(rule, {findings, reason = null}) {
    const severity = rule.severity ?? CHECK_STATUS.FAILED
    const broken = findings.filter(finding => finding.outcome === 'broken')
    const unknown = findings.filter(finding => finding.outcome === 'unknown')
    const statuses = [
        broken.length ? severity : null,
        unknown.length || reason ? CHECK_STATUS.CANNOT_VERIFY : null
    ]
    const status = WORST_FIRST.find(candidate => statuses.includes(candidate)) ?? CHECK_STATUS.PASSED
    const listed = [...broken, ...unknown].map(({outcome, ...finding}) => ({
        ...finding,
        status: outcome === 'broken' ? severity : CHECK_STATUS.CANNOT_VERIFY
    }))
    return result(status, status === CHECK_STATUS.CANNOT_VERIFY ? reason : null, listed)
}

function statusOf(rule, context) {
    const notApplicable = rule.notApplicable?.(context)
    if (notApplicable) return result(CHECK_STATUS.NOT_APPLICABLE, notApplicable)
    if (rule.switchable && context.switches[rule.key] === false) return result(CHECK_STATUS.SWITCHED_OFF)
    // With Hold fuldendt switched off, Niveau and Kategori run on what's there.
    if (rule.waitsForFullSquads && context.switches.complete !== false) {
        const basicSquads = context.responses.basicSquads
        if (!basicSquads) return result(null)
        if (!basicSquads.every(isFull)) return result(CHECK_STATUS.WAITING, WAITING_FOR_FULL_SQUADS)
    }
    const response = context.responses[rule.response]
    if (response === undefined || response === null) return result(null)
    return ranResult(rule, rule.run(response, context))
}

// input:
//   teamRound: {round, seasonStartYear, squads: [{isPaperSquad, effectiveTier, reserves}]}
//   switches: {complete, finalRound, qualification}; false switches the Check off, anything else leaves it on
//   responses: each Check's server response as the editor has it, null or missing until it answers:
//     basicSquads (validateBasicSquads), crossSquads (validateTeamRoundCrossSquads), squads (validateSquads),
//     reserves (validateReserves), paperSquads (validatePaperSquads), finalRound (validateFinalRoundEligibility),
//     qualification (validateQualificationEligibility), playoff (validatePlayoffParticipation),
//     tooYoung (validateTooYoungPlayers)
// Returns [{key, status, reason, findings}] in CHECKS order. reason: Danish text for Kan ikke kontrolleres, Venter and
// Gælder ikke, else null. findings: [{squadIndex?, refId?, name?, status, message, guidance?, uncountedRounds?}], the
// broken ones first; each finding's status is the Check's severity or cannot-verify.
// status null: the Check would run, but its request hasn't answered (loading or failed, a panel state).
export function checkStatuses({teamRound = null, switches = {}, responses = {}} = {}) {
    const context = {teamRound, switches, responses}
    return RULES.map(rule => ({key: rule.key, ...statusOf(rule, context)}))
}
