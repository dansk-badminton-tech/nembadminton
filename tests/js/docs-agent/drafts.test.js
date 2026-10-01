import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    outcomeComment,
    parseGitStatus,
    parseNameStatus,
    reviewDrafts,
} from '../../../scripts/docs-agent/drafts.js'

const today = '2026-10-01'
const announcement = 'resources/help/news/2026-10-01-nye-holdrunder.md'
const builtOk = {ok: true, output: 'built in 3s'}

function review(overrides = {}) {
    return reviewDrafts({
        label: 'docs:announcement',
        trigger: 'label',
        committed: [],
        working: [{change: 'added', path: announcement}],
        validationErrors: [],
        build: builtOk,
        today,
        ...overrides,
    })
}

test('git status lines become changes, including untracked files', () => {
    assert.deepEqual(parseGitStatus(`?? ${announcement}\n M resources/help/guides/opret.md\n D resources/help/guides/gammel.md\n`), [
        {change: 'added', path: announcement},
        {change: 'modified', path: 'resources/help/guides/opret.md'},
        {change: 'deleted', path: 'resources/help/guides/gammel.md'},
    ])
})

test('a renamed file in git status is a rename to its new path', () => {
    assert.deepEqual(parseGitStatus('R  resources/help/guides/a.md -> resources/help/guides/b.md\n'), [
        {change: 'renamed', path: 'resources/help/guides/b.md', from: 'resources/help/guides/a.md'},
    ])
})

test('a clean working tree has no changes', () => {
    assert.deepEqual(parseGitStatus(''), [])
})

test('git diff --name-status lines become the changes committed on the branch', () => {
    assert.deepEqual(parseNameStatus(`A\t${announcement}\nM\tresources/help/guides/opret.md\nD\tresources/help/guides/gammel.md\nR100\tresources/help/guides/a.md\tresources/help/guides/b.md\n`), [
        {change: 'added', path: announcement},
        {change: 'modified', path: 'resources/help/guides/opret.md'},
        {change: 'deleted', path: 'resources/help/guides/gammel.md'},
        {change: 'renamed', path: 'resources/help/guides/b.md', from: 'resources/help/guides/a.md'},
    ])
})

test('one new announcement dated today that validates and builds is publishable', () => {
    const result = review()

    assert.equal(result.publishable, true)
    assert.deepEqual(result.changed, [{change: 'added', path: announcement}])
    assert.equal(result.commitMessage, 'docs: draft release announcement (agent)')
    assert.deepEqual(result.openQuestions, [])
    assert.deepEqual(result.offLabel, [])
    assert.deepEqual(result.problems, [])
})

test('unresolved TODO(agent) markers are open questions, not problems', () => {
    const result = review({
        validationErrors: [
            `${announcement}: unresolved TODO(agent) marker "Hvornår udrulles ændringen?"`,
            `${announcement}: unresolved TODO(agent) marker "Gælder det også for ungdomshold?"`,
        ],
    })

    assert.equal(result.publishable, true)
    assert.deepEqual(result.openQuestions, [
        {file: announcement, question: 'Hvornår udrulles ændringen?'},
        {file: announcement, question: 'Gælder det også for ungdomshold?'},
    ])
})

test('markers in Help documents the branch does not touch are not open questions of the drafts', () => {
    const result = review({
        validationErrors: ['resources/help/guides/opret.md: unresolved TODO(agent) marker "Hvilken knap?"'],
    })

    assert.equal(result.publishable, true)
    assert.deepEqual(result.openQuestions, [])
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

test('no announcement on the branch blocks publishing', () => {
    const result = review({working: []})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, ['No Release Announcement was drafted.'])
})

test('docs:announcement changes no User Guide', () => {
    const result = review({
        working: [
            {change: 'added', path: announcement},
            {change: 'modified', path: 'resources/help/guides/opret.md'},
        ],
    })

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        'docs:announcement drafts no User Guide, but these guides changed:\n- resources/help/guides/opret.md',
    ])
})

test('files outside User Guides and Release Announcements may not change', () => {
    const result = review({
        working: [
            {change: 'added', path: announcement},
            {change: 'modified', path: 'resources/help/pages/kom-i-gang.md'},
            {change: 'modified', path: 'package.json'},
        ],
    })

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        'Only User Guides and Release Announcements may change, but these files changed too:\n'
            + '- resources/help/pages/kom-i-gang.md\n'
            + '- package.json',
    ])
})

test('an edited announcement from master is history, not a draft', () => {
    const published = 'resources/help/news/2026-09-22-visuelt-loeft.md'
    const result = review({working: [{change: 'modified', path: published}]})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        'No Release Announcement was drafted.',
        `Release Announcements already on master are history and may not change:\n- ${published}`,
    ])
})

test('an announcement not dated with the day of the run blocks publishing', () => {
    const stale = 'resources/help/news/2026-09-30-nye-holdrunder.md'
    const result = review({working: [{change: 'added', path: stale}]})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [`${stale} must be dated ${today}, the day of the run.`])
})

const guide = 'resources/help/guides/opret-og-klargoer-en-holdrunde.md'

test('docs:guide may update the guide that covers the workflow and add an announcement in one commit', () => {
    const working = [{change: 'modified', path: guide}, {change: 'added', path: announcement}]
    const result = review({label: 'docs:guide', working})

    assert.equal(result.publishable, true)
    assert.equal(result.commitMessage, 'docs: draft user guide (agent)')
    assert.deepEqual(result.changed, working)
    assert.deepEqual(result.problems, [])
})

test('docs:guide may create a new guide for a new workflow', () => {
    const result = review({
        label: 'docs:guide',
        working: [{change: 'added', path: 'resources/help/guides/ny-arbejdsgang.md'}, {change: 'added', path: announcement}],
    })

    assert.equal(result.publishable, true)
})

test('docs:guide without a new or updated guide blocks publishing', () => {
    const result = review({label: 'docs:guide'})

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, ['No User Guide was drafted or updated.'])
})

const earlierAnnouncement = 'resources/help/news/2026-09-30-nye-holdrunder.md'

test('switching to docs:guide after an announcement run adds only the guide', () => {
    const result = review({
        label: 'docs:guide',
        committed: [{change: 'added', path: earlierAnnouncement}],
        working: [{change: 'modified', path: guide}],
    })

    assert.equal(result.publishable, true)
    assert.deepEqual(result.problems, [])
})

test('a run may still improve the announcement drafted earlier on the branch', () => {
    const result = review({
        label: 'docs:guide',
        committed: [{change: 'added', path: earlierAnnouncement}],
        working: [{change: 'modified', path: guide}, {change: 'modified', path: earlierAnnouncement}],
    })

    assert.equal(result.publishable, true)
})

test('a second announcement on the branch is a duplicate', () => {
    const result = review({
        label: 'docs:guide',
        committed: [{change: 'added', path: earlierAnnouncement}],
        working: [{change: 'modified', path: guide}, {change: 'added', path: announcement}],
    })

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        `The branch already has a Release Announcement; update it instead of adding another:\n- ${earlierAnnouncement}\n- ${announcement}`,
    ])
})

test('switching to docs:announcement after a guide run adds nothing and lists the guide changes that no longer fit', () => {
    const newGuide = 'resources/help/guides/ny-arbejdsgang.md'
    const result = review({
        committed: [
            {change: 'modified', path: guide},
            {change: 'added', path: newGuide},
            {change: 'added', path: earlierAnnouncement},
        ],
        working: [],
    })

    assert.equal(result.publishable, true)
    assert.deepEqual(result.changed, [])
    assert.deepEqual(result.offLabel, [guide, newGuide])
    assert.deepEqual(result.problems, [])
})

test('the docs agent never deletes or renames files', () => {
    const result = review({
        label: 'docs:guide',
        committed: [{change: 'added', path: earlierAnnouncement}],
        working: [
            {change: 'modified', path: guide},
            {change: 'deleted', path: 'resources/help/guides/gammel.md'},
            {change: 'renamed', path: 'resources/help/guides/b.md', from: 'resources/help/guides/a.md'},
        ],
    })

    assert.equal(result.publishable, false)
    assert.deepEqual(result.problems, [
        'The docs agent never deletes or renames files, but these were:\n'
            + '- resources/help/guides/gammel.md\n'
            + '- resources/help/guides/a.md -> resources/help/guides/b.md',
    ])
})

test('the comment for a run with nothing to add says nothing was pushed and lists the files that no longer fit', () => {
    const comment = outcomeComment(review({
        committed: [{change: 'modified', path: guide}, {change: 'added', path: earlierAnnouncement}],
        working: [],
    }))

    assert.match(comment, /already has what `docs:announcement` needs\. Nothing was pushed\./)
    assert.match(comment, /no longer fit `docs:announcement`/)
    assert.match(comment, /never deletes files/)
    assert.match(comment, /- `resources\/help\/guides\/opret-og-klargoer-en-holdrunde\.md`/)
})

test('a /docs follow-up commits under its own message', () => {
    const result = review({
        trigger: 'comment',
        committed: [{change: 'added', path: announcement}],
        working: [{change: 'modified', path: announcement}],
    })

    assert.equal(result.publishable, true)
    assert.equal(result.commitMessage, 'docs: apply /docs comment (agent)')
})

test('the summary comment names the file and lists open questions', () => {
    const comment = outcomeComment(review({
        validationErrors: [`${announcement}: unresolved TODO(agent) marker "Hvornår udrulles ændringen?"`],
    }))

    assert.match(comment, /pushed `docs: draft release announcement \(agent\)`/)
    assert.match(comment, /- `resources\/help\/news\/2026-10-01-nye-holdrunder\.md` \(new\)/)
    assert.match(comment, /- \[ \] `resources\/help\/news\/2026-10-01-nye-holdrunder\.md`: Hvornår udrulles ændringen\?/)
    assert.match(comment, /CI fails until/)
    assert.match(comment, /`\/docs <answers>`/)
})

test('the summary comment says when there are no open questions', () => {
    const comment = outcomeComment(review())

    assert.match(comment, /No open questions/)
    assert.doesNotMatch(comment, /CI fails until/)
})

test('the blocked comment says nothing was pushed and includes the errors', () => {
    const comment = outcomeComment(review({build: {ok: false, output: 'error during build: boom'}}))

    assert.match(comment, /Nothing was pushed/)
    assert.match(comment, /```\nyarn build failed:\nerror during build: boom\n```/)
})
