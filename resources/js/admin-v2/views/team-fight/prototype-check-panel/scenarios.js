// PROTOTYPE (throwaway, map #331 / ticket #332): fixture Lineups for the check panel variants.
// One real Lineup can't show all four "no result" reasons at once, so each scenario sets every Check by hand.
//
// A Check here: {key, name, rule, perPlayer, switchable, defaultOn, status, reason, details, whenUnblocked}
// status: ok | error | warning | not-applicable | off | waiting | cannot-verify | pending
// The four "no result" statuses are not-applicable, off, waiting and cannot-verify.

// description: one sentence on what the Check tests; round 4 shows it when a row is unfolded.
const CHECKS = {
    complete: {name: 'Hold fuldendt', rule: '', switchable: true, defaultOn: true,
        description: 'Alle hold har en spiller på hver plads.'},
    level: {name: 'Niveau', rule: '§38 stk. 2–3',
        description: 'Ingen spiller står på et lavere hold end en spiller med færre point.'},
    category: {name: 'Kategori', rule: '§38 stk. 2–3',
        description: 'Ingen spiller spiller for højt i sin kategori på holdet.'},
    reserves: {name: 'Reserver', rule: '§43',
        description: 'En reserve har ikke flere point end spilleren, vedkommende erstatter.'},
    paper: {name: 'Papirhold', rule: '§38 stk. 7',
        description: 'Spillerne på papirholdet har spillet i kategorien og på samme eller et lavere hold før.'},
    finalRound: {name: 'Tidligere hold', rule: '§38 stk. 8', switchable: true, defaultOn: false,
        description: 'Spillerne har stået på samme eller et lavere hold tidligere i sæsonen.'},
    qualification: {name: 'Kvalifikation', rule: '§27 stk. 5', switchable: true, defaultOn: false,
        description: 'Spillerne opfylder rundenummerkravene til kvalifikationen.'},
    playoff: {name: 'Slutspilskrav', rule: '§32 stk. 1',
        description: 'Spillerne har spillet nok kampe for klubben til at spille slutspil.'},
    tooYoung: {name: 'For ung', rule: '§31 stk. 1', perPlayer: true,
        description: 'Spillerne fylder 15 år senest 31.12. i det år, sæsonen starter.'},
    noPoints: {name: 'Ingen point i kategorien', rule: '', perPlayer: true,
        description: 'Spillerne har point på ranglisten i den kategori, de står i.'},
}

export const NOT_APPLICABLE = {
    reserves: 'Opstillingen har ingen reserver.',
    paper: 'Opstillingen har intet papirhold.',
    finalRound: 'Gælder kun runde 11–12 i 1.–3. division og Danmarksserien og runde 12 i Badmintonligaen.',
    qualification: 'Gælder kun runde 12 i 2.–3. division og Danmarksserien.',
    playoff: 'Gælder kun slutspillet i 1. division (fra runde 8) og Badmintonligaen (fra runde 10).',
}

const WAITING = 'Venter på, at alle hold er fuldt besat.'

function build(states) {
    return Object.entries(CHECKS).map(([key, base]) => ({key, switchable: false, defaultOn: true, perPlayer: false, details: [], ...base, ...states[key]}))
}

export const SCENARIOS = [
    {
        key: 'basic',
        name: 'Runde 4, 3. division – et hold mangler en spiller',
        checks: build({
            complete: {status: 'error', details: ['Hold 2 mangler en spiller i 2. HD.']},
            level: {status: 'waiting', reason: WAITING, whenUnblocked: {status: 'ok'}},
            category: {status: 'waiting', reason: WAITING, whenUnblocked: {status: 'error', details: ['Hold 1: Mads Holm spiller for højt i HS.']}},
            reserves: {status: 'not-applicable', reason: NOT_APPLICABLE.reserves},
            paper: {status: 'not-applicable', reason: NOT_APPLICABLE.paper},
            finalRound: {status: 'not-applicable', reason: NOT_APPLICABLE.finalRound},
            qualification: {status: 'not-applicable', reason: NOT_APPLICABLE.qualification},
            playoff: {status: 'not-applicable', reason: NOT_APPLICABLE.playoff},
            tooYoung: {status: 'ok'},
            noPoints: {status: 'warning', details: ['Hold 3: Sofie Lund har ingen point i DD.']},
        }),
    },
    {
        key: 'final',
        name: 'Runde 11, 2. division – fuldt hold med reserver',
        checks: build({
            complete: {status: 'ok'},
            level: {status: 'ok'},
            category: {status: 'error', details: [
                'Hold 1: Mads Holm spiller for højt i HS.',
                'Hold 1: Anna Friis spiller for højt i DS.',
                'Hold 2: Peter Lind spiller for højt i 1. HD.',
            ]},
            reserves: {status: 'warning', details: ['Hold 2: Reserven Emil Berg har flere point end Jonas Krog, som han erstatter.']},
            paper: {status: 'not-applicable', reason: NOT_APPLICABLE.paper},
            finalRound: {status: 'warning', details: [
                'Hold 1: Karl Dam har ikke stået på samme eller et lavere hold tidligere i sæsonen.',
                'Hold 1: Line Bech har ikke stået på samme eller et lavere hold tidligere i sæsonen.',
            ]},
            qualification: {status: 'not-applicable', reason: NOT_APPLICABLE.qualification},
            playoff: {status: 'not-applicable', reason: NOT_APPLICABLE.playoff},
            tooYoung: {status: 'error', details: ['Hold 3: Ida Møller er ikke fyldt 15 år senest 31.12.2026.']},
            noPoints: {status: 'ok'},
        }),
    },
    {
        key: 'missing',
        name: 'Runde 12 uden dato og række – papirhold',
        checks: build({
            complete: {status: 'ok'},
            level: {status: 'ok'},
            category: {status: 'ok'},
            reserves: {status: 'not-applicable', reason: NOT_APPLICABLE.reserves},
            paper: {status: 'ok'},
            finalRound: {status: 'cannot-verify', reason: 'Angiv datoen i holdrundens indstillinger.'},
            qualification: {status: 'cannot-verify', reason: 'Angiv holdets række under Rediger holdet.'},
            playoff: {status: 'cannot-verify', reason: 'Angiv holdets række under Rediger holdet.'},
            tooYoung: {status: 'ok'},
            noPoints: {status: 'ok'},
        }),
    },
]

// "Denne holdrunde": maps the editor's own computed state onto the prototype's Checks. Rough; good enough to react to.
export function liveScenario(vm) {
    const ternary = (value, waiting) => {
        if (value === null) return waiting ? {status: 'waiting', reason: WAITING} : {status: 'pending'}
        return {status: value ? 'error' : 'ok'}
    }
    const server = (show, state, notApplicable) => {
        if (!show) return {status: 'not-applicable', reason: notApplicable}
        if (state.status === 'failed') return {status: 'cannot-verify', reason: 'Kontrollen kunne ikke gennemføres. Prøv igen.'}
        if (state.status !== 'complete') return {status: 'pending'}
        const findings = (state.findings ?? []).filter(f => f.status !== 'MET' && f.status !== 'NOT_APPLICABLE')
        if (findings.some(f => f.status === 'CANNOT_VERIFY')) return {status: 'cannot-verify', reason: 'Spilleberettigelse kan ikke verificeres fuldt ud.'}
        return {status: findings.length ? 'warning' : 'ok', details: findings.map(f => `Hold ${f.squadIndex + 1}: ${f.name}`)}
    }
    const tooYoung = vm.tooYoungPlayers ?? []
    return {
        key: 'live',
        name: 'Denne holdrunde',
        checks: build({
            complete: vm.ignoreIncompleteTeam ? {status: 'off'} : ternary(vm.resolveIncompleteTeam, false),
            level: ternary(vm.resolveInvalidLevel, true),
            category: ternary(vm.resolveInvalidCategory, true),
            reserves: vm.hasReserves
                ? {status: (vm.reserveFindings ?? []).length ? 'warning' : 'ok'}
                : {status: 'not-applicable', reason: NOT_APPLICABLE.reserves},
            paper: vm.hasPaperSquad
                ? (vm.paperSquadValidation === null ? {status: 'pending'} : {status: (vm.paperSquadValidation.players ?? []).length ? 'warning' : 'ok'})
                : {status: 'not-applicable', reason: NOT_APPLICABLE.paper},
            finalRound: server(vm.showFinalRoundControl, vm.finalRoundValidation, NOT_APPLICABLE.finalRound),
            qualification: server(vm.showQualificationControl, vm.qualificationValidation, NOT_APPLICABLE.qualification),
            playoff: server(vm.showPlayoffParticipationControl, vm.playoffParticipationValidation, NOT_APPLICABLE.playoff),
            tooYoung: {status: tooYoung.length ? 'error' : 'ok', details: tooYoung.map(p => `${p.name ?? p.refId} er for ung.`)},
            noPoints: {status: 'pending', reason: 'Ikke koblet til prototypen; se markeringerne i holdtabellen.'},
        }),
    }
}

// Applies the coach's switches. Hold fuldendt off releases the Checks waiting on it.
export function applySwitches(checks, switches) {
    const completeOff = switches.complete === false
    return checks.map(check => {
        if (check.switchable && switches[check.key] === false && check.status !== 'not-applicable') {
            return {...check, status: 'off', reason: 'Slået fra. Slå til med kontakten.', details: []}
        }
        if (completeOff && check.status === 'waiting') {
            return {...check, reason: undefined, ...(check.whenUnblocked ?? {status: 'ok'})}
        }
        return check
    })
}

// Shared presentation per status, so the variants differ in structure, not in vocabulary.
export const STATUS = {
    ok: {text: 'OK', tag: 'is-success', icon: 'check-circle', group: 'ok'},
    error: {text: 'Fejl', tag: 'is-danger', icon: 'alert-circle', group: 'action'},
    warning: {text: 'Advarsel', tag: 'is-warning', icon: 'alert', group: 'action'},
    'cannot-verify': {text: 'Kan ikke kontrolleres', tag: 'is-warning is-light', icon: 'help-circle', group: 'action'},
    waiting: {text: 'Venter', tag: 'is-light', icon: 'timer-sand', group: 'waiting'},
    pending: {text: 'Afventer', tag: 'is-light', icon: 'minus-circle-outline', group: 'waiting'},
    off: {text: 'Slået fra', tag: 'is-light', icon: 'cancel', group: 'inactive'},
    'not-applicable': {text: 'Gælder ikke', tag: 'is-white', icon: 'minus', group: 'inactive'},
}
