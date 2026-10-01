import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    draftComment,
    parseGitStatus,
    parseValidationErrors,
    reviewAnnouncementDraft,
} from '../../../scripts/docs-agent/announcement-draft.js'

const today = '2026-10-01'
const announcement = 'resources/help/news/2026-10-01-nye-holdrunder.md'
const builtOk = {ok: true, output: 'built in 3s'}

function review(overrides = {}) {
    return reviewAnnouncementDraft({
        changes: [{status: '??', path: announcement}],
        validationErrors: [],
        build: builtOk,
        today,
        ...overrides,
    })
}

test('git status lines become changes, including untracked files', () => {
    assert.deepEqual(parseGitStatus(`?? ${announcement}\n M resources/help/guides/opret.md\n`), [
        {status: '??', path: announcement},
        {status: ' M', path: 'resources/help/guides/opret.md'},
    ])
})

test('a clean working tree has no changes', () => {
    assert.deepEqual(parseGitStatus(''), [])
})

test('validator output becomes one error per line, without the heading', () => {
    const output = 'Help documentation validation failed:\nnews/a.md: summary is required\nnews/b.md: title is required\n'
    assert.deepEqual(parseValidationErrors(output), ['news/a.md: summary is required', 'news/b.md: title is required'])
})

test('passing validator output has no errors', () => {
    assert.deepEqual(parseValidationErrors(''), [])
})

test('one new announcement dated today that validates and builds is publishable', () => {
    assert.deepEqual(review(), {publishable: true, file: announcement, openQuestions: [], problems: []})
})

test('unresolved TODO(agent) markers are open questions, not problems', () => {
    const result = review({
        validationErrors: [
            `${announcement}: unresolved TODO(agent) marker "Hvornår udrulles ændringen?"`,
            `${announcement}: unresolved TODO(agent) marker "Gælder det også for ungdomshold?"`,
        ],
    })

    assert.equal(result.publishable, true)
    assert.deepEqual(result.openQuestions, ['Hvornår udrulles ændringen?', 'Gælder det også for ungdomshold?'])
})

test('any other validation error blocks publishing', () => {
    const result = review({
        validationErrors: [
            `${announcement}: unresolved TODO(agent) marker "Hvornår udrulles ændringen?"`,
            `${announcement}: summary is required`,
            `${announcement}: malformed TODO(agent) marker; use <!-- TODO(agent): <question> -->`,
        ],
    })

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        `yarn docs:validate failed:\n${announcement}: summary is required\n${announcement}: malformed TODO(agent) marker; use <!-- TODO(agent): <question> -->`,
    ])
})

test('a build failure blocks publishing and carries the build output', () => {
    const result = review({build: {ok: false, output: 'error during build: boom'}})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, ['yarn build failed:\nerror during build: boom'])
})

test('no changes at all blocks publishing', () => {
    const result = review({changes: []})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, ['No Release Announcement was drafted.'])
})

test('changes outside a single new announcement block publishing', () => {
    const result = review({
        changes: [
            {status: '??', path: announcement},
            {status: ' M', path: 'resources/help/guides/opret.md'},
            {status: '??', path: 'resources/help/news/2026-10-01-andet.md'},
        ],
    })

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        'Only one new Release Announcement may change, but these files changed too:\n'
            + '- resources/help/guides/opret.md\n'
            + '- resources/help/news/2026-10-01-andet.md',
    ])
})

test('an edited existing announcement is not a new announcement', () => {
    const result = review({changes: [{status: ' M', path: 'resources/help/news/2026-09-22-visuelt-loeft.md'}]})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        'No Release Announcement was drafted.',
        'Only one new Release Announcement may change, but these files changed too:\n'
            + '- resources/help/news/2026-09-22-visuelt-loeft.md',
    ])
})

test('an announcement not dated with the day of the run blocks publishing', () => {
    const stale = 'resources/help/news/2026-09-30-nye-holdrunder.md'
    const result = review({changes: [{status: '??', path: stale}]})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [`${stale} must be dated ${today}, the day of the run.`])
})

test('the summary comment names the file and lists open questions', () => {
    const comment = draftComment(review({
        validationErrors: [`${announcement}: unresolved TODO(agent) marker "Hvornår udrulles ændringen?"`],
    }))

    assert.match(comment, /drafted a Release Announcement/)
    assert.match(comment, /`resources\/help\/news\/2026-10-01-nye-holdrunder\.md`/)
    assert.match(comment, /- \[ \] Hvornår udrulles ændringen\?/)
    assert.match(comment, /CI fails until/)
})

test('the summary comment says when there are no open questions', () => {
    const comment = draftComment(review())

    assert.match(comment, /No open questions/)
    assert.doesNotMatch(comment, /CI fails until/)
})

test('the blocked comment says nothing was pushed and includes the errors', () => {
    const comment = draftComment(review({build: {ok: false, output: 'error during build: boom'}}))

    assert.match(comment, /Nothing was pushed/)
    assert.match(comment, /```\nyarn build failed:\nerror during build: boom\n```/)
})
