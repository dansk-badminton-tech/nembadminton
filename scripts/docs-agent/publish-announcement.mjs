// Last step of the docs agent's CI mode (see .agents/skills/document-feature/SKILL.md).
// Checks the uncommitted Release Announcement draft, then either commits and pushes it to the
// PR head branch or pushes nothing. Either way it comments on the PR with the outcome.
import {spawnSync} from 'node:child_process'
import {readFileSync, writeFileSync} from 'node:fs'
import {join} from 'node:path'
import {
    draftComment,
    parseGitStatus,
    parseValidationErrors,
    reviewAnnouncementDraft,
} from './announcement-draft.js'

const commitMessage = 'docs: draft release announcement (agent)'
const buildOutputLines = 40

function run(command, args, options = {}) {
    const result = spawnSync(command, args, {encoding: 'utf8', maxBuffer: 64 * 1024 * 1024, ...options})
    return {ok: result.status === 0, output: `${result.stdout ?? ''}${result.stderr ?? ''}`.trim()}
}

function lastLines(text, count) {
    return text.split('\n').slice(-count).join('\n')
}

if (process.env.GITHUB_ACTIONS !== 'true' || !process.env.GITHUB_EVENT_PATH || !process.env.GITHUB_HEAD_REF) {
    console.error('publish-announcement only runs in the document-feature workflow on a pull_request event.')
    process.exit(2)
}

const pullRequest = JSON.parse(readFileSync(process.env.GITHUB_EVENT_PATH, 'utf8')).pull_request
const build = run('yarn', ['build'])
const review = reviewAnnouncementDraft({
    changes: parseGitStatus(run('git', ['status', '--porcelain', '--untracked-files=all']).output),
    validationErrors: parseValidationErrors(run('node', ['scripts/validate-help-documents.mjs']).output),
    build: {ok: build.ok, output: lastLines(build.output, buildOutputLines)},
    today: new Date().toISOString().slice(0, 10),
})

if (review.publishable) {
    for (const [command, args] of [
        ['git', ['add', '--', review.file]],
        ['git', ['commit', '--message', commitMessage]],
        ['git', ['push', 'origin', `HEAD:refs/heads/${process.env.GITHUB_HEAD_REF}`]],
    ]) {
        const step = run(command, args)

        if (!step.ok) {
            review.publishable = false
            review.problems.push(`${command} ${args.join(' ')} failed:\n${step.output}`)
            break
        }
    }
}

const comment = draftComment(review)
const commented = run('gh', ['pr', 'comment', String(pullRequest.number), '--body-file', '-'], {input: comment})
console.log(comment)

if (!commented.ok) {
    console.error(`Could not comment on the PR:\n${commented.output}`)
}

if (process.env.RUNNER_TEMP) {
    writeFileSync(join(process.env.RUNNER_TEMP, 'docs-agent-outcome'), review.publishable ? 'pushed' : 'blocked')
}

process.exitCode = review.publishable ? 0 : 1
