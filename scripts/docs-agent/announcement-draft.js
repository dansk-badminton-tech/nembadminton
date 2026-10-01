// Decides whether the docs agent's Release Announcement draft may be pushed to the PR branch,
// and writes the PR comment that reports the outcome.

const announcementPath = /^resources\/help\/news\/(\d{4}-\d{2}-\d{2})-[a-z0-9-]+\.md$/
const openQuestionError = /: unresolved TODO\(agent\) marker "(.*)"$/

export function parseGitStatus(output) {
    return output
        .split('\n')
        .filter(line => line.trim() !== '')
        .map(line => ({status: line.slice(0, 2), path: line.slice(3)}))
}

export function reviewAnnouncementDraft({changes, validationErrors, build, today}) {
    const problems = []
    const drafted = changes
        .filter(change => change.status === '??')
        .map(change => ({path: change.path, date: change.path.match(announcementPath)?.[1]}))
        .find(draft => draft.date !== undefined)
    const file = drafted?.path ?? null
    const unexpected = changes.filter(change => change.path !== file)

    if (file === null) {
        problems.push('No Release Announcement was drafted.')
    } else if (drafted.date !== today) {
        problems.push(`${file} must be dated ${today}, the day of the run.`)
    }

    if (unexpected.length > 0) {
        problems.push([
            'Only one new Release Announcement may change, but these files changed too:',
            ...unexpected.map(change => `- ${change.path}`),
        ].join('\n'))
    }

    const openQuestions = validationErrors
        .filter(error => error.startsWith(`${file}: `))
        .map(error => error.match(openQuestionError)?.[1])
        .filter(question => question !== undefined)
    const blockingErrors = validationErrors.filter(error => !openQuestionError.test(error))

    if (blockingErrors.length > 0) {
        problems.push(['yarn docs:validate failed:', ...blockingErrors].join('\n'))
    }

    if (!build.ok) {
        problems.push(`yarn build failed:\n${build.output}`)
    }

    return {publishable: problems.length === 0, file, openQuestions, problems}
}

export function outcomeComment(review) {
    if (!review.publishable) {
        return [
            'The docs agent could not publish its Release Announcement draft. Nothing was pushed.',
            '',
            ...review.problems.map(problem => `\`\`\`\n${problem}\n\`\`\``),
        ].join('\n')
    }

    const lines = [
        `The docs agent drafted a Release Announcement in \`${review.file}\`.`,
        '',
    ]

    if (review.openQuestions.length === 0) {
        lines.push('No open questions.')
    } else {
        lines.push(
            'Open questions, marked with `TODO(agent)` in the draft:',
            '',
            ...review.openQuestions.map(question => `- [ ] ${question}`),
            '',
            'CI fails until each marker is replaced with verified text.',
        )
    }

    return lines.join('\n')
}
