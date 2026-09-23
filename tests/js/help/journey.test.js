import {test} from 'node:test'
import assert from 'node:assert/strict'
import {buildGuideOverview} from '../../../resources/js/admin-v2/help/journey.js'

function guide(slug, journey) {
    return {slug, title: slug, journey}
}

test('a single optional guide is shown under its stage without a step guide', () => {
    const scenarios = guide('scenarier', {stage: 'lineup', role: 'optional'})

    assert.deepEqual(buildGuideOverview([scenarios]), {
        stages: [{key: 'lineup', label: 'Lav holdopstillingen', number: 3, step: null, optional: [scenarios]}],
        troubleshooting: [],
        other: [],
    })
})

test('the full journey places steps, branches, troubleshooting, and guides outside the journey', () => {
    const setup = guide('opret', {stage: 'setup', role: 'step'})
    const cancellations = guide('afbud', {stage: 'cancellations', role: 'step'})
    const lineup = guide('holdopstilling', {stage: 'lineup', role: 'step'})
    const scenarios = guide('scenarier', {stage: 'lineup', role: 'optional'})
    const missingPlayer = guide('manglende-spiller', {stage: 'lineup', role: 'troubleshooting'})
    const share = guide('del', {stage: 'sharing', role: 'step'})
    const send = guide('send', {stage: 'sharing', role: 'optional'})
    const profile = guide('profil', undefined)

    assert.deepEqual(buildGuideOverview([profile, send, share, missingPlayer, scenarios, lineup, cancellations, setup]), {
        stages: [
            {key: 'setup', label: 'Opret og klargør', number: 1, step: setup, optional: []},
            {key: 'cancellations', label: 'Håndtér afbud', number: 2, step: cancellations, optional: []},
            {key: 'lineup', label: 'Lav holdopstillingen', number: 3, step: lineup, optional: [scenarios]},
            {key: 'sharing', label: 'Del holdopstillingen', number: 4, step: share, optional: [send]},
        ],
        troubleshooting: [{guide: missingPlayer, stage: 'Lav holdopstillingen'}],
        other: [profile],
    })
})
