import {test} from 'node:test'
import assert from 'node:assert/strict'
import {checkPanel} from '../../../resources/js/admin-v2/views/team-fight/checks/check-panel.mjs'
import {CHECKS, checkStatuses} from '../../../resources/js/admin-v2/views/team-fight/checks/check-status.mjs'

const ran = (key, status, extra = {}) => ({key, status, reason: null, findings: [], ...extra})

const keysOf = panel => Object.fromEntries(panel.sections.map(section => [section.key, section.rows.map(row => row.key)]))

test('an empty TeamRound puts every Check in Venter, even after a request failure', () => {
    const panel = checkPanel({
        statuses: checkStatuses({teamRound: {squads: []}}),
        requests: {basicSquads: 'failed', crossSquads: 'failed', playoff: 'failed'}
    })
    assert.deepEqual(keysOf(panel), {
        pending: [], action: [], waiting: CHECKS.map(check => check.key), off: [], ok: []
    })
    assert.deepEqual(panel.notApplicable, [])
    assert.ok(panel.sections.find(section => section.key === 'waiting').rows.every(row => row.label === 'Venter' && row.state === null))
})

test('the panel lists Checks under Kræver handling (Fejl, Advarsel, Kan ikke kontrolleres), Venter, Slået fra and OK', () => {
    const panel = checkPanel({statuses: [
        ran('complete', 'passed'),
        ran('level', 'waiting', {reason: 'Venter på, at alle hold er fuldt besat.'}),
        ran('category', 'cannot-verify'),
        ran('reserves', 'warning'),
        ran('paperSquads', 'not-applicable', {reason: 'Opstillingen har intet papirhold.'}),
        ran('finalRound', 'switched-off'),
        ran('qualification', 'not-applicable', {reason: 'Gælder kun runde 12 i 2.–3. division og Danmarksserien.'}),
        ran('playoff', 'failed'),
        ran('tooYoung', 'passed')
    ]})
    assert.deepEqual(panel.sections.map(section => [section.key, section.title]), [
        ['pending', null], ['action', 'Kræver handling'], ['waiting', 'Venter'], ['off', 'Slået fra'], ['ok', 'OK']
    ])
    assert.deepEqual(keysOf(panel), {
        pending: [], action: ['playoff', 'reserves', 'category'], waiting: ['level'], off: ['finalRound'], ok: ['complete', 'tooYoung']
    })
    assert.deepEqual(panel.notApplicable.map(row => [row.key, row.reason]), [
        ['paperSquads', 'Opstillingen har intet papirhold.'],
        ['qualification', 'Gælder kun runde 12 i 2.–3. division og Danmarksserien.']
    ])
})

test('a row carries its Check\'s name, paragraph, label and switch', () => {
    const [row] = checkPanel({statuses: [ran('finalRound', 'switched-off')]}).sections.find(section => section.key === 'off').rows
    assert.deepEqual([row.name, row.rule, row.label, row.switchable, row.perPlayer], ['Tidligere hold', '§38 stk. 8', 'Slået fra', true, false])
    const [young] = checkPanel({statuses: [ran('tooYoung', 'passed')]}).sections.find(section => section.key === 'ok').rows
    assert.equal(young.perPlayer, true)
})

test('a Check without a status is loading until one of its requests fails', () => {
    const statuses = [ran('complete', null), ran('level', null), ran('reserves', null)]
    const loading = checkPanel({statuses, requests: {basicSquads: 'loading'}}).sections[0]
    assert.deepEqual(loading.rows.map(row => [row.key, row.state]), [['complete', 'loading'], ['level', 'loading'], ['reserves', 'loading']])
    const failed = checkPanel({statuses, requests: {basicSquads: 'done', crossSquads: 'failed', reserves: 'loading'}}).sections[0]
    assert.deepEqual(failed.rows.map(row => [row.key, row.state]), [['complete', 'loading'], ['level', 'failed'], ['reserves', 'loading']])
    assert.deepEqual(checkPanel({statuses, requests: {basicSquads: 'failed'}}).sections[0].rows.map(row => row.state), ['failed', 'failed', 'loading'])
})

test('a Check with a status stays in its section while its request reloads', () => {
    const panel = checkPanel({statuses: [ran('reserves', 'warning'), ran('tooYoung', 'passed')], requests: {reserves: 'loading', tooYoung: 'done'}})
    const [reserves] = panel.sections.find(section => section.key === 'action').rows
    const [young] = panel.sections.find(section => section.key === 'ok').rows
    assert.deepEqual([reserves.state, reserves.refreshing, young.refreshing], [null, true, false])
})
