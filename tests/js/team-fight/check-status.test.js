import {test} from 'node:test'
import assert from 'node:assert/strict'
import {CHECKS, STATUS_LABEL, checkStatuses} from '../../../resources/js/admin-v2/views/team-fight/checks/check-status.mjs'

function statusOf(key, input) {
    return checkStatuses(input).find(check => check.key === key)
}

test('every Check waits for the first Squad, before and after empty responses arrive', () => {
    const teamRound = {round: 9, seasonStartYear: 2026, squads: []}
    const emptyResponses = {
        basicSquads: [], crossSquads: {findings: [], unknownPairs: []},
        squads: {playersWithoutPoints: [], findings: []}, reserves: [],
        paperSquads: {missingRound: false, players: []}, tooYoung: [],
        finalRound: {notApplicableReason: 'ROUND_NOT_COVERED', players: []},
        qualification: {notApplicableReason: 'ROUND_NOT_COVERED', players: []},
        playoff: {notApplicableReason: 'TIER_NOT_COVERED', players: []}
    }
    for (const responses of [{}, emptyResponses]) {
        for (const switches of [{}, {complete: false, finalRound: false, qualification: false}]) {
            assert.deepEqual(checkStatuses({teamRound, responses, switches}), CHECKS.map(({key}) => ({
                key, status: 'waiting', reason: 'Tilføj et hold til holdrunden for at starte tjekkene.', findings: []
            })))
        }
    }
})

test('Hold fuldendt fails with one finding per Squad that lacks players', () => {
    const check = statusOf('complete', {responses: {basicSquads: [{index: 0, spotsFulfilled: true}, {index: 1, spotsFulfilled: false}]}})
    assert.equal(check.status, 'failed')
    assert.deepEqual(check.findings.map(finding => finding.message), ['Hold 2 mangler spillere.'])
})

test('adding the first Squad resumes the usual Check Statuses and removing it waits again', () => {
    const teamRound = {squads: [{}]}
    const responses = {basicSquads: [{index: 0, spotsFulfilled: false}]}
    assert.equal(statusOf('complete', {teamRound, responses}).status, 'failed')
    assert.equal(statusOf('level', {teamRound, responses}).status, 'waiting')
    assert.equal(statusOf('reserves', {teamRound, responses}).status, 'not-applicable')
    assert.equal(statusOf('complete', {teamRound, responses, switches: {complete: false}}).status, 'switched-off')
    teamRound.squads = []
    assert.ok(checkStatuses({teamRound, responses}).every(check => check.status === 'waiting' && check.findings.length === 0))
})

test('Hold fuldendt passes when every Squad is full and is Switched Off by its switch', () => {
    const responses = {basicSquads: [{index: 0, spotsFulfilled: false}]}
    assert.equal(statusOf('complete', {responses: {basicSquads: [{index: 0, spotsFulfilled: true}]}}).status, 'passed')
    assert.equal(statusOf('complete', {responses: {basicSquads: [{index: 0, spotsFulfilled: true, missingPlayerInCategory: true}]}}).status, 'failed')
    assert.deepEqual(statusOf('complete', {switches: {complete: false}, responses}), {key: 'complete', status: 'switched-off', reason: null, findings: []})
})

const INCOMPLETE = [{index: 0, spotsFulfilled: true}, {index: 1, spotsFulfilled: false}]
const FULL = [{index: 0, spotsFulfilled: true}, {index: 1, spotsFulfilled: true}]
const LEVEL_FINDING = {name: 'Mads Holm', refId: 'm1', belowPlayer: [{name: 'Jonas Krog', refId: 'j1'}, {name: 'Jonas Krog', refId: 'j1'}, {name: 'Emil Berg', refId: 'e1'}]}
const CATEGORY_FINDING = {name: 'Anna Friis', refId: 'a1', category: 'DS', belowPlayer: [{name: 'Line Bech', refId: 'l1'}]}
// validateSquads' answer (Kategori).
const order = (findings, playersWithoutPoints = []) => ({playersWithoutPoints, findings})

// validateTeamRoundCrossSquads' answer: today's findings and the pairs a higher player's missing points decide.
function crossSquads(findings, unknownPairs = []) {
    return {findings, unknownPairs}
}

function unknownPair(category, higher = {refId: 'c1', name: 'Carl Dam', squadIndex: 0}) {
    return {player: {refId: 'b1', name: 'Bo Lund', squadIndex: 1}, higherPlayer: higher, category}
}

test('Niveau and Kategori wait until every Squad is full', () => {
    for (const key of ['level', 'category']) {
        const check = statusOf(key, {responses: {basicSquads: INCOMPLETE, crossSquads: crossSquads([LEVEL_FINDING]), squads: order([CATEGORY_FINDING])}})
        assert.deepEqual(check, {key, status: 'waiting', reason: 'Venter på, at alle hold er fuldt besat.', findings: []})
        assert.equal(statusOf(key, {responses: {}}).status, null, 'unknown until Hold fuldendt answers')
    }
})

test('Niveau and Kategori run on what is there when Hold fuldendt is switched off', () => {
    const responses = {basicSquads: INCOMPLETE, crossSquads: crossSquads([]), squads: order([])}
    assert.equal(statusOf('level', {switches: {complete: false}, responses}).status, 'passed')
    assert.equal(statusOf('category', {switches: {complete: false}, responses}).status, 'passed')
    assert.equal(statusOf('level', {switches: {complete: false}, responses: {crossSquads: crossSquads([])}}).status, 'passed', 'no need to wait for Hold fuldendt')
})

test('Niveau fails with each player on a too low Squad, naming the players below once', () => {
    const check = statusOf('level', {responses: {basicSquads: FULL, crossSquads: crossSquads([LEVEL_FINDING])}})
    assert.equal(check.status, 'failed')
    assert.deepEqual(check.findings, [{refId: 'm1', name: 'Mads Holm', status: 'failed',
        message: 'Mads Holm spiller på et for lavt rangeret hold i forhold til Jonas Krog, Emil Berg.'}])
})

test('Niveau is Kan ikke kontrolleres when a higher player\'s missing points decide a pair, one finding per pair', () => {
    const check = statusOf('level', {responses: {basicSquads: FULL, crossSquads: crossSquads([], [unknownPair('HS'), unknownPair('HD')])}})
    assert.deepEqual(check, {
        key: 'level',
        status: 'cannot-verify',
        reason: 'En spiller på et højere hold har ingen point på holdrundens rangliste i en kategori, vedkommende spiller.',
        findings: [{squadIndex: 1, refId: 'b1', name: 'Bo Lund', status: 'cannot-verify',
            message: 'Carl Dam på hold 1 har ingen point i HS, HD, så det kan ikke afgøres, om Bo Lund må stå på hold 2.'}]
    })
})

test('Niveau fails when another pair breaks the rule, listing the unknown pairs after', () => {
    const check = statusOf('level', {responses: {basicSquads: FULL, crossSquads: crossSquads([LEVEL_FINDING], [unknownPair('HS')])}})
    assert.equal(check.status, 'failed')
    assert.equal(check.reason, null)
    assert.deepEqual(check.findings.map(finding => finding.status), ['failed', 'cannot-verify'])
})

test('Kategori fails with each player too high in their category', () => {
    const check = statusOf('category', {responses: {basicSquads: FULL, squads: order([CATEGORY_FINDING])}})
    assert.equal(check.status, 'failed')
    assert.deepEqual(check.findings.map(finding => finding.message), ['Anna Friis spiller for højt i sin kategori (DS) i forhold til Line Bech.'])
    assert.equal(statusOf('category', {responses: {basicSquads: FULL, squads: order([])}}).status, 'passed')
})

test('Kategori is Kan ikke kontrolleres with every player without points in their category', () => {
    const playersWithoutPoints = [
        {squadIndex: 0, refId: 's1', name: 'Sofie Lund', category: 'DS'},
        {squadIndex: 1, refId: 'p1', name: 'Peter Mø', category: 'MD'}
    ]
    const check = statusOf('category', {responses: {basicSquads: FULL, squads: order([], playersWithoutPoints)}})
    assert.deepEqual(check, {
        key: 'category',
        status: 'cannot-verify',
        reason: 'Kategori kan først kontrolleres, når alle spillere har point i de kategorier, de spiller.',
        findings: [
            {squadIndex: 0, refId: 's1', name: 'Sofie Lund', status: 'cannot-verify',
                message: 'Sofie Lund har ingen point i DS.'},
            {squadIndex: 1, refId: 'p1', name: 'Peter Mø', status: 'cannot-verify',
                message: 'Peter Mø har ingen point i MD.'}
        ]
    })
})

test('Niveau and Kategori leave out Youth Players and players with a youth partner, as today', () => {
    const youth = {...LEVEL_FINDING, isYouthPlayer: true}
    assert.equal(statusOf('level', {responses: {basicSquads: FULL, crossSquads: crossSquads([youth])}}).status, 'passed')
    for (const extra of [{isYouthPlayer: true}, {hasYouthPlayerPartner: true}]) {
        assert.equal(statusOf('category', {responses: {basicSquads: FULL, squads: order([{...CATEGORY_FINDING, ...extra}])}}).status, 'passed')
    }
})

const RESERVE = {id: 'r1', reserve: {refId: 'e1', name: 'Emil Berg'}, replaced: {refId: 'j1', name: 'Jonas Krog'}}
const RESERVE_FINDING = {squadIndex: 1, categoryName: '1. HD', reserve: {refId: 'e1', name: 'Emil Berg', points: 2100}, replaced: {refId: 'j1', name: 'Jonas Krog', points: 1900}}

test('Reserver does not apply to a Lineup without Reserve Players', () => {
    const teamRound = {round: 4, squads: [{reserves: []}, {}]}
    assert.deepEqual(statusOf('reserves', {teamRound, responses: {reserves: [RESERVE_FINDING]}}),
        {key: 'reserves', status: 'not-applicable', reason: 'Opstillingen har ingen reserver.', findings: []})
})

test('Reserver warns about a Reserve Player with more points than the player they replace', () => {
    const teamRound = {round: 4, squads: [{reserves: []}, {reserves: [RESERVE]}]}
    const check = statusOf('reserves', {teamRound, responses: {reserves: [RESERVE_FINDING]}})
    assert.equal(check.status, 'warning')
    assert.deepEqual(check.findings, [{squadIndex: 1, refId: 'e1', name: 'Emil Berg', status: 'warning',
        message: 'Hold 2: Reserven Emil Berg har flere point end Jonas Krog i 1. HD (2100 mod 1900). Er holdet stærkere end planlagt (§43 stk. 3)?'}])
    assert.equal(statusOf('reserves', {teamRound, responses: {reserves: []}}).status, 'passed')
    assert.equal(statusOf('reserves', {teamRound, responses: {}}).status, null)
})

const PAPER_ROUND = {round: 5, squads: [{isPaperSquad: false}, {isPaperSquad: true}]}
const UNCOUNTED = [{teamRoundId: 't2', name: 'Runde 2', round: 2}]
const paperPlayer = overrides => ({squadIndex: 1, refId: 'p1', name: 'Sofie Lund', unplayedCategories: [], matchCount: 1,
    requiredMatchCount: 1, teamName: 'Holdkamp 3', uncountedRounds: [], ...overrides})

test('Papirhold does not apply to a Lineup without a Paper Squad', () => {
    const check = statusOf('paperSquads', {teamRound: {round: 5, squads: [{isPaperSquad: false}]}, responses: {}})
    assert.deepEqual(check, {key: 'paperSquads', status: 'not-applicable', reason: 'Opstillingen har intet papirhold.', findings: []})
})

test('Papirhold cannot be verified without a round number', () => {
    const check = statusOf('paperSquads', {teamRound: PAPER_ROUND, responses: {paperSquads: {missingRound: true, players: []}}})
    assert.equal(check.status, 'cannot-verify')
    assert.equal(check.reason, 'Udfyld Runde nr. i holdrundens indstillinger.')
    assert.deepEqual(check.findings, [])
})

test('Papirhold fails for a player who breaks the rule for sure', () => {
    const unplayed = paperPlayer({unplayedCategories: ['DD']})
    const tooFew = paperPlayer({refId: 'p2', name: 'Ida Møller', matchCount: 0, requiredMatchCount: 2, uncountedRounds: UNCOUNTED})
    const check = statusOf('paperSquads', {teamRound: PAPER_ROUND, responses: {paperSquads: {missingRound: false, players: [unplayed, tooFew]}}})
    assert.equal(check.status, 'failed')
    assert.deepEqual(check.findings.map(finding => [finding.refId, finding.status]), [['p1', 'failed'], ['p2', 'failed']])
    assert.equal(check.findings[0].message, 'Hold 2 (papirhold): Sofie Lund har ikke spillet DD tidligere i sæsonen.')
    assert.equal(statusOf('paperSquads', {teamRound: PAPER_ROUND, responses: {paperSquads: {missingRound: false, players: []}}}).status, 'passed')
})

test('Papirhold cannot verify a player whose missing matches may be in rounds without the Team linked', () => {
    const player = paperPlayer({matchCount: 0, requiredMatchCount: 1, uncountedRounds: UNCOUNTED})
    const check = statusOf('paperSquads', {teamRound: PAPER_ROUND, responses: {paperSquads: {missingRound: false, players: [player]}}})
    assert.equal(check.status, 'cannot-verify')
    assert.equal(check.reason, 'Tilknyt holdet i de tidligere holdrunder, der står under spillerne.')
    assert.equal(check.findings[0].status, 'cannot-verify')
    assert.deepEqual(check.findings[0].uncountedRounds, UNCOUNTED)
})

const historyPlayer = overrides => ({squadIndex: 0, refId: 'k1', name: 'Karl Dam', status: 'SATISFIED', incompleteReasons: [], uncountedRounds: [], ...overrides})
const NOT_MET = historyPlayer({status: 'REQUIREMENT_NOT_MET'})
const UNKNOWN_DATE = historyPlayer({refId: 'l1', name: 'Line Bech', status: 'CANNOT_VERIFY', incompleteReasons: ['MISSING_GAME_DATE']})
const round = (number, tier, extra = {}) => ({round: number, squads: [{isPaperSquad: false, effectiveTier: tier, ...extra}]})

const answer = (...players) => ({notApplicableReason: null, players})
const notApplicable = notApplicableReason => ({notApplicableReason, players: []})

test('a history Check does not apply when the server says no Squad is covered, and says why', () => {
    const reasons = {
        finalRound: {
            ROUND_NOT_COVERED: 'Gælder kun runde 11–12 i 1.–3. division og Danmarksserien og runde 12 i Badmintonligaen.',
            TIER_NOT_COVERED: 'Gælder kun 1.–3. division, Danmarksserien og Badmintonligaen.'
        },
        qualification: {
            ROUND_NOT_COVERED: 'Gælder kun runde 12 i 2.–3. division og Danmarksserien.',
            TIER_NOT_COVERED: 'Gælder kun 2.–3. division og Danmarksserien.'
        },
        playoff: {
            ROUND_NOT_COVERED: 'Gælder kun slutspillet i 1. division (fra runde 8) og Badmintonligaen (fra runde 10).',
            TIER_NOT_COVERED: 'Gælder kun 1. division og Badmintonligaen.'
        }
    }
    for (const [key, texts] of Object.entries(reasons)) {
        for (const [reason, text] of Object.entries({...texts, PAPER_SQUADS_ONLY: 'Opstillingen har kun papirhold.'})) {
            assert.deepEqual(statusOf(key, {teamRound: round(12, '2. division'), responses: {[key]: notApplicable(reason)}}),
                {key, status: 'not-applicable', reason: text, findings: []}, `${key} ${reason}`)
        }
    }
})

test('a history Check decides nothing about applicability from the round and tier itself', () => {
    for (const key of ['finalRound', 'qualification', 'playoff']) {
        assert.equal(statusOf(key, {teamRound: round(5, 'Serie 1')}).status, null, key)
        assert.equal(statusOf(key, {teamRound: round(5, 'Serie 1', {isPaperSquad: true})}).status, null, key)
        assert.equal(statusOf(key, {teamRound: round(5, 'Serie 1'), responses: {[key]: answer()}}).status, 'passed', key)
    }
})

test('Not Applicable wins over Switched Off, which wins over running', () => {
    const teamRound = round(12, '2. division')
    assert.equal(statusOf('finalRound', {teamRound, switches: {finalRound: false}, responses: {finalRound: notApplicable('ROUND_NOT_COVERED')}}).status, 'not-applicable')
    const off = statusOf('finalRound', {teamRound, switches: {finalRound: false}, responses: {finalRound: answer(NOT_MET)}})
    assert.deepEqual(off, {key: 'finalRound', status: 'switched-off', reason: null, findings: []})
    assert.equal(statusOf('qualification', {teamRound, switches: {qualification: false}}).status, 'switched-off', 'before the server answers')
})

test('Tidligere hold fails for a player who has not played on the same or a lower Squad, even when another cannot be verified', () => {
    const check = statusOf('finalRound', {teamRound: round(12, '2. division'), switches: {finalRound: true}, responses: {finalRound: answer(UNKNOWN_DATE, NOT_MET, historyPlayer({refId: 'x'}))}})
    assert.equal(check.status, 'failed')
    assert.equal(check.reason, null)
    assert.deepEqual(check.findings.map(finding => [finding.refId, finding.status]), [['k1', 'failed'], ['l1', 'cannot-verify']])
    assert.equal(check.findings[0].message, 'Hold 1: Karl Dam har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8).')
})

test('Tidligere hold cannot be verified with the first thing to fill in, and passes when every player meets it', () => {
    const teamRound = round(12, '2. division')
    const linked = historyPlayer({refId: 'u1', status: 'CANNOT_VERIFY', uncountedRounds: UNCOUNTED})
    const check = statusOf('finalRound', {teamRound, switches: {finalRound: true}, responses: {finalRound: answer(UNKNOWN_DATE, linked, {...UNKNOWN_DATE, refId: 'l2'})}})
    assert.equal(check.status, 'cannot-verify')
    assert.equal(check.reason, 'Angiv datoen i holdrundens indstillinger, så tidligere holdrunder kan findes.')
    assert.deepEqual(check.findings[0].guidance, ['Angiv datoen i holdrundens indstillinger, så tidligere holdrunder kan findes.'])
    assert.equal(check.findings[0].message, 'Hold 1: Line Bech – spilleberettigelse kan ikke verificeres fuldt ud (§38 stk. 8).')
    assert.deepEqual(check.findings[1].uncountedRounds, UNCOUNTED)
    const passed = statusOf('finalRound', {teamRound, switches: {finalRound: true}, responses: {finalRound: answer(historyPlayer({}), historyPlayer({status: 'NOT_APPLICABLE'}))}})
    assert.deepEqual(passed, {key: 'finalRound', status: 'passed', reason: null, findings: []})
})

test('a missing round, tier, team or season gives Kan ikke kontrolleres with one thing to fill in at a time', () => {
    const missing = historyPlayer({status: 'CANNOT_VERIFY', incompleteReasons: ['MISSING_ROUND', 'UNKNOWN_TIER', 'MISSING_TEAM', 'MISSING_SEASON']})
    const check = statusOf('qualification', {teamRound: round(null, null), switches: {qualification: true}, responses: {qualification: answer(missing)}})
    assert.equal(check.status, 'cannot-verify')
    assert.equal(check.reason, 'Udfyld Runde nr. i holdrundens indstillinger.')
    assert.deepEqual(check.findings[0].guidance, [
        'Udfyld Runde nr. i holdrundens indstillinger.',
        'Angiv holdets række under Rediger holdet, eller på sæsonens hold, hvis et hold er tilknyttet.',
        'Tilknyt sæsonens hold via Rediger holdet → Tilknyt hold i denne holdrunde.',
        'Holdrunden mangler sæson, så kontakt support for at få den rettet.'
    ])
    const tierOnly = historyPlayer({status: 'CANNOT_VERIFY', incompleteReasons: ['UNKNOWN_TIER'], uncountedRounds: UNCOUNTED})
    assert.equal(statusOf('qualification', {teamRound: round(12, null), switches: {qualification: true}, responses: {qualification: answer(tierOnly)}}).reason,
        'Angiv holdets række under Rediger holdet, eller på sæsonens hold, hvis et hold er tilknyttet.')
})

test('Kvalifikation warns rather than fails', () => {
    const check = statusOf('qualification', {teamRound: round(12, 'Danmarksserien'), switches: {qualification: true}, responses: {qualification: answer(UNKNOWN_DATE, NOT_MET)}})
    assert.equal(check.status, 'warning', 'Advarsel wins over Kan ikke kontrolleres')
    assert.deepEqual(check.findings.map(finding => finding.status), ['warning', 'cannot-verify'])
    assert.equal(check.findings[0].message, 'Hold 1: Karl Dam opfylder ikke de kontrollerede rundenummerkrav (§27 stk. 5); Nembadminton vurderer ikke kalenderårsalternativet.')
    assert.equal(statusOf('qualification', {teamRound: round(12, 'Danmarksserien'), switches: {qualification: true}, responses: {qualification: answer()}}).status, 'passed')
})

test('Slutspilskrav fails for sure breaches', () => {
    const notMet = {...NOT_MET, matchCount: 1, requiredMatchCount: 3, playedByDeadline: true}
    const check = statusOf('playoff', {teamRound: round(8, '1. division'), responses: {playoff: answer(notMet)}})
    assert.equal(check.status, 'failed')
    assert.equal(check.findings[0].message, 'Hold 1: Karl Dam har 1 af 3 kampe i sæsonen; 20. oktober-kravet er opfyldt (§32 stk. 1).')
    assert.equal(statusOf('playoff', {teamRound: round(8, '1. division'), responses: {playoff: answer(historyPlayer({}))}}).status, 'passed')
})

test('Slutspilskrav cannot be verified with what to fill in for earlier rounds', () => {
    const unknown = {...historyPlayer({status: 'CANNOT_VERIFY', incompleteReasons: ['MISSING_HISTORY_DATE']}), matchCount: 2, requiredMatchCount: 3, playedByDeadline: null}
    const check = statusOf('playoff', {teamRound: round(10, 'Badmintonligaen'), responses: {playoff: answer(unknown)}})
    assert.equal(check.status, 'cannot-verify')
    assert.equal(check.reason, 'Angiv spilledatoen i de tidligere holdrunder herunder.')
    assert.equal(check.findings[0].message, 'Hold 1: Karl Dam har 2 af 3 kampe – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).')
})

test('For ung fails for each player who is too young', () => {
    const teamRound = {round: 3, seasonStartYear: 2026, squads: [{}]}
    const check = statusOf('tooYoung', {teamRound, responses: {tooYoung: [{id: 1, refId: 'i1', name: 'Ida Møller'}]}})
    assert.equal(check.status, 'failed')
    assert.deepEqual(check.findings, [{refId: 'i1', name: 'Ida Møller', status: 'failed', message: 'Ida Møller er ikke fyldt 15 år senest 31.12.2026 (§31 stk. 1).'}])
    assert.equal(statusOf('tooYoung', {teamRound, responses: {tooYoung: []}}).status, 'passed')
})

test('every Check is listed once, in the panel\'s order', () => {
    assert.deepEqual(checkStatuses({}).map(check => check.key),
        ['complete', 'level', 'category', 'reserves', 'paperSquads', 'finalRound', 'qualification', 'playoff', 'tooYoung'])
})

test('the panel gets each Check\'s name, paragraph, severity and switch, and each status\'s Danish label', () => {
    assert.deepEqual(CHECKS.map(check => [check.key, check.name, check.rule, check.severity, check.switchable]), [
        ['complete', 'Hold fuldendt', null, 'failed', true],
        ['level', 'Niveau', '§38 stk. 4', 'failed', false],
        ['category', 'Kategori', '§38 stk. 2–3', 'failed', false],
        ['reserves', 'Reserver', '§43', 'warning', false],
        ['paperSquads', 'Papirhold', '§38 stk. 7', 'failed', false],
        ['finalRound', 'Tidligere hold', '§38 stk. 8', 'failed', true],
        ['qualification', 'Kvalifikation', '§27 stk. 5', 'warning', true],
        ['playoff', 'Slutspilskrav', '§32 stk. 1', 'failed', false],
        ['tooYoung', 'For ung', '§31', 'failed', false]
    ])
    assert.ok(CHECKS.every(check => check.description.endsWith('.')))
    assert.deepEqual(STATUS_LABEL, {
        'passed': 'OK', 'failed': 'Fejl', 'warning': 'Advarsel', 'cannot-verify': 'Kan ikke kontrolleres',
        'waiting': 'Venter', 'switched-off': 'Slået fra', 'not-applicable': 'Gælder ikke'
    })
})

test('the panel gets each Check\'s User Guide, note and whether it reads earlier Team Rounds', () => {
    assert.deepEqual(Object.fromEntries(CHECKS.map(check => [check.key, [check.guide, check.note, check.readsEarlierRounds]])), {
        complete: [null, null, false],
        level: [null, null, false],
        category: [null, null, false],
        reserves: [null, null, false],
        paperSquads: [{slug: 'tilknyt-holdet-i-tidligere-runder'},
            'En ulovlig spiller på et papirhold er en skærpende omstændighed: klubbens underliggende hold kan blive taberdømt (§54 stk. 7).', true],
        finalRound: [{slug: 'tilknyt-holdet-i-tidligere-runder'}, null, true],
        qualification: [{slug: 'kontroller-spillerkrav-i-kvalifikation'}, null, true],
        playoff: [{slug: 'lav-og-kontroller-den-officielle-holdopstilling', hash: '#kontrol-af-slutspilskravet'}, null, true],
        tooYoung: [null, null, false]
    })
})

test('each Check names the requests its status waits on', () => {
    assert.deepEqual(Object.fromEntries(CHECKS.map(check => [check.key, check.requests])), {
        complete: ['basicSquads'],
        level: ['basicSquads', 'crossSquads'],
        category: ['basicSquads', 'squads'],
        reserves: ['reserves'],
        paperSquads: ['paperSquads'],
        finalRound: ['finalRound'],
        qualification: ['qualification'],
        playoff: ['playoff'],
        tooYoung: ['tooYoung']
    })
})

test('a Check whose request has not answered has no status, so the panel shows its own loading or retry state', () => {
    assert.equal(statusOf('complete', {responses: {}}).status, null)
    assert.equal(statusOf('complete', {}).status, null)
})
