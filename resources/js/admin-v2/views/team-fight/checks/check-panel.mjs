// How the check panel lists the Check Statuses from check-status.mjs: in sections, with the Checks that don't apply in
// their own folded group. A Check without a status is loading or its request failed (panel states, not Check Statuses).
// Framework-free, so node:test can load it.

import {CHECKS, CHECK_STATUS, STATUS_LABEL} from './check-status.mjs'

const ACTION_ORDER = [CHECK_STATUS.FAILED, CHECK_STATUS.WARNING, CHECK_STATUS.CANNOT_VERIFY]

const SECTIONS = [
    // Checks still loading or whose request failed, above the others, without a heading.
    {key: 'pending', title: null, holds: status => status === null},
    {key: 'action', title: 'Kræver handling', holds: status => ACTION_ORDER.includes(status)},
    {key: 'waiting', title: 'Venter', holds: status => status === CHECK_STATUS.WAITING},
    {key: 'off', title: 'Slået fra', holds: status => status === CHECK_STATUS.SWITCHED_OFF},
    {key: 'ok', title: 'OK', holds: status => status === CHECK_STATUS.PASSED}
]

// requests: {[request]: 'loading' | 'failed' | 'done'}, keyed like CHECKS[].requests.
function rowOf(status, requests) {
    const check = CHECKS.find(candidate => candidate.key === status.key)
    const failed = check.requests.some(request => requests[request] === 'failed')
    const loading = check.requests.some(request => requests[request] === 'loading')
    return {
        ...check,
        ...status,
        label: status.status === null ? null : STATUS_LABEL[status.status],
        state: status.status === null ? (failed ? 'failed' : 'loading') : null,
        // The status is the last answer, and a new one is on its way.
        refreshing: status.status !== null && loading
    }
}

// statuses: the output of checkStatuses. Returns {sections: [{key, title, rows}], notApplicable: rows}; every section
// is listed, also when empty. Kræver handling lists Fejl, then Advarsel, then Kan ikke kontrolleres.
export function checkPanel({statuses, requests = {}}) {
    const rows = statuses.map(status => rowOf(status, requests))
    const sections = SECTIONS.map(({key, title, holds}) => ({key, title, rows: rows.filter(row => holds(row.status))}))
    sections.find(section => section.key === 'action').rows
        .sort((a, b) => ACTION_ORDER.indexOf(a.status) - ACTION_ORDER.indexOf(b.status))
    return {sections, notApplicable: rows.filter(row => row.status === CHECK_STATUS.NOT_APPLICABLE)}
}
