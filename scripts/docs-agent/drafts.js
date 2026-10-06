// Decides whether the docs agent's Help drafts may be pushed to the PR branch, and writes the PR
// comment that reports the outcome. See .agents/skills/document-feature/CI.md.

const guidePath = /^resources\/help\/guides\/[a-z0-9-]+\.md$/
const announcementPath = /^resources\/help\/news\/(\d{4}-\d{2}-\d{2})-[a-z0-9-]+\.md$/
const openQuestionError = /^(.*): unresolved TODO\(agent\) marker "(.*)"$/

const commitMessages = {
    'docs:guide': 'docs: draft user guide (agent)',
    'docs:announcement': 'docs: draft release announcement (agent)',
}

// Runs started by a `/docs <answers>` comment or `/docs refresh`, rather than by the label.
const followUps = {
    comment: {
        commitMessage: 'docs: apply /docs comment (agent)',
        unchanged: 'The `/docs` comment changed nothing in the drafts. Nothing was pushed.',
    },
    refresh: {
        commitMessage: 'docs: refresh drafts (agent)',
        unchanged: '`/docs refresh` found the drafts up to date. Nothing was pushed.',
    },
}

function change(status, path, from) {
    const kind = status.includes('R') ? 'renamed'
        : status.includes('D') ? 'deleted'
            : status === '??' || status.includes('A') ? 'added'
                : 'modified'

    return kind === 'renamed' ? {change: kind, path, from} : {change: kind, path}
}

// `git status --porcelain` output: the changes this run made.
export function parseGitStatus(output) {
    return output
        .split('\n')
        .filter(line => line.trim() !== '')
        .map(line => {
            const [from, path] = line.slice(3).split(' -> ')
            return path === undefined ? change(line.slice(0, 2), from) : change(line.slice(0, 2), path, from)
        })
}

// `git diff --name-status <merge base> HEAD` output: the changes already committed on the branch.
export function parseNameStatus(output) {
    return output
        .split('\n')
        .filter(line => line.trim() !== '')
        .map(line => {
            const [status, first, second] = line.split('\t')
            return second === undefined ? change(status, first) : change(status, second, first)
        })
}

function list(heading, paths) {
    return [heading, ...paths.map(path => `- ${path}`)].join('\n')
}

// A rename is a deletion plus an addition, so a moved draft passes the same checks as both.
function splitRenames(changes) {
    return changes.flatMap(c => (c.change === 'renamed'
        ? [{change: 'deleted', path: c.from}, {change: 'added', path: c.path}]
        : [c]))
}

export function reviewDrafts({label, trigger, committed: committedChanges, working: workingChanges, validationErrors, build, today}) {
    const problems = []
    const committed = splitRenames(committedChanges)
    const working = splitRenames(workingChanges)
    const isEdit = c => c.change === 'added' || c.change === 'modified'
    const isGuideEdit = c => isEdit(c) && guidePath.test(c.path)
    const isNewAnnouncement = c => c.change === 'added' && announcementPath.test(c.path)
    const branch = [...committed, ...working]
    const branchGuides = branch.filter(isGuideEdit)
    const committedAnnouncements = committed.filter(isNewAnnouncement).map(c => c.path)
    const followUp = Object.hasOwn(followUps, trigger) ? followUps[trigger] : null
    // A /docs follow-up may move the announcement drafted on this branch to another date.
    const isMovedDraft = c => followUp !== null
        && c.change === 'deleted'
        && committedAnnouncements.includes(c.path)
    const movedDrafts = working.filter(isMovedDraft).map(c => c.path)
    const newAnnouncements = working.filter(isNewAnnouncement)
    const draftedAnnouncements = [
        ...committedAnnouncements.filter(path => !movedDrafts.includes(path)).map(path => ({path})),
        ...newAnnouncements,
    ]
    const removed = working.filter(c => c.change === 'deleted' && !isMovedDraft(c))
    const outsideHelp = working.filter(c => isEdit(c) && !guidePath.test(c.path) && !announcementPath.test(c.path))
    const changedGuides = working.filter(isGuideEdit)
    const editedHistory = working.filter(c => c.change === 'modified'
        && announcementPath.test(c.path)
        && !draftedAnnouncements.some(drafted => drafted.path === c.path))

    if (removed.length > 0) {
        problems.push(list(
            'The docs agent never deletes files, but these were deleted or renamed:',
            removed.map(c => c.path),
        ))
    }

    if (label === 'docs:guide' && branchGuides.length === 0) {
        problems.push('No User Guide was drafted or updated.')
    }

    if (draftedAnnouncements.length === 0) {
        problems.push('No Release Announcement was drafted.')
    } else if (draftedAnnouncements.length > 1) {
        problems.push(list(
            'The branch already has a Release Announcement; update it instead of adding another:',
            draftedAnnouncements.map(c => c.path),
        ))
    }

    for (const drafted of newAnnouncements) {
        if (trigger === 'label' && drafted.path.match(announcementPath)[1] !== today) {
            problems.push(`${drafted.path} must be dated ${today}, the day of the run.`)
        }
    }

    if (label === 'docs:announcement' && changedGuides.length > 0) {
        problems.push(list('docs:announcement drafts no User Guide, but these guides changed:', changedGuides.map(c => c.path)))
    }

    if (outsideHelp.length > 0) {
        problems.push(list('Only User Guides and Release Announcements may change, but these files changed too:', outsideHelp.map(c => c.path)))
    }

    if (editedHistory.length > 0) {
        problems.push(list('Release Announcements already on master are history and may not change:', editedHistory.map(c => c.path)))
    }

    const drafts = new Set(branch.map(c => c.path))
    const openQuestions = validationErrors
        .map(error => error.match(openQuestionError))
        .filter(match => match !== null && drafts.has(match[1]))
        .map(([, file, question]) => ({file, question}))
    const blockingErrors = validationErrors.filter(error => !openQuestionError.test(error))

    if (blockingErrors.length > 0) {
        problems.push(['yarn docs:validate failed:', ...blockingErrors].join('\n'))
    }

    if (!build.ok) {
        problems.push(`yarn build failed:\n${build.output}`)
    }

    return {
        publishable: problems.length === 0,
        label,
        commitMessage: followUp?.commitMessage ?? commitMessages[label],
        unchangedMessage: followUp?.unchanged ?? `The branch already has what \`${label}\` needs. Nothing was pushed.`,
        changed: working,
        openQuestions,
        // Earlier guide changes are kept; the owner decides whether to remove them.
        offLabel: label === 'docs:announcement' ? committed.filter(isGuideEdit).map(c => c.path) : [],
        problems,
    }
}

export function outcomeComment(review) {
    if (!review.publishable) {
        return [
            `The docs agent could not publish its \`${review.label}\` drafts. Nothing was pushed.`,
            '',
            ...review.problems.map(problem => `\`\`\`\n${problem}\n\`\`\``),
        ].join('\n')
    }

    const lines = review.changed.length === 0
        ? [review.unchangedMessage, '']
        : [
            `The docs agent pushed \`${review.commitMessage}\`:`,
            '',
            ...review.changed.map(c => `- \`${c.path}\` (${{added: 'new', modified: 'updated', deleted: 'moved'}[c.change]})`),
            '',
        ]

    if (review.offLabel.length > 0) {
        lines.push(
            `These User Guide changes on the branch don't fit \`${review.label}\`. The docs agent never deletes files: remove them yourself, or switch the label back to \`docs:guide\` to keep them.`,
            '',
            ...review.offLabel.map(path => `- \`${path}\``),
            '',
        )
    }

    if (review.openQuestions.length === 0) {
        lines.push('No open questions.')
    } else {
        lines.push(
            'Open questions, marked with `TODO(agent)` in the drafts:',
            '',
            ...review.openQuestions.map(({file, question}) => `- [ ] \`${file}\`: ${question}`),
            '',
            'CI fails until each marker is replaced with verified text. Answer with a `/docs <answers>` comment.',
        )
    }

    return lines.join('\n')
}
