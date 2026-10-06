// Last step of the docs agent's CI mode (see .agents/skills/document-feature/CI.md).
// Checks the uncommitted Help drafts against the documentation label, then either commits and
// pushes them to the PR head branch or pushes nothing. Either way it comments on the PR with the
// outcome. The document-feature workflow sets the environment variables read below.
import {spawnSync} from 'node:child_process'
import {existsSync, writeFileSync} from 'node:fs'
import {join} from 'node:path'
import {validateHelpDocuments} from '../../resources/js/admin-v2/help/markdown.js'
import {loadHelpDocuments} from '../load-help-documents.mjs'
import {outcomeComment, parseGitStatus, parseNameStatus, reviewDrafts} from './drafts.js'

const buildOutputLines = 40
const {GITHUB_ACTIONS, DOCS_LABEL, DOCS_TRIGGER, PR_NUMBER, HEAD_REF, BASE_REF, RUNNER_TEMP} = process.env

function run(command, args, options = {}) {
    const result = spawnSync(command, args, {encoding: 'utf8', maxBuffer: 64 * 1024 * 1024, ...options})
    return {ok: result.status === 0, output: `${result.stdout ?? ''}${result.stderr ?? ''}`.trimEnd()}
}

function lastLines(text, count) {
    return text.split('\n').slice(-count).join('\n')
}

async function validationErrors() {
    try {
        return validateHelpDocuments(await loadHelpDocuments())
    } catch (error) {
        return [error.message]
    }
}

if (GITHUB_ACTIONS !== 'true' || !DOCS_LABEL || !DOCS_TRIGGER || !PR_NUMBER || !HEAD_REF || !BASE_REF) {
    console.error('publish-drafts only runs in the document-feature workflow.')
    process.exit(2)
}

const outcomeFile = RUNNER_TEMP ? join(RUNNER_TEMP, 'docs-agent-outcome') : null

if (outcomeFile && existsSync(outcomeFile)) {
    console.error('publish-drafts already ran in this job; it publishes once per run.')
    process.exit(2)
}

const mergeBase = run('git', ['merge-base', `origin/${BASE_REF}`, 'HEAD'])

if (!mergeBase.ok) {
    console.error(`Could not find the merge base with origin/${BASE_REF}:\n${mergeBase.output}`)
    process.exit(2)
}

const build = run('yarn', ['build'])
const review = reviewDrafts({
    label: DOCS_LABEL,
    trigger: DOCS_TRIGGER,
    committed: parseNameStatus(run('git', ['diff', '--name-status', '--find-renames', mergeBase.output, 'HEAD', '--', 'resources/help']).output),
    working: parseGitStatus(run('git', ['status', '--porcelain', '--untracked-files=all']).output),
    validationErrors: await validationErrors(),
    build: {ok: build.ok, output: lastLines(build.output, buildOutputLines)},
    today: new Date().toISOString().slice(0, 10),
})

if (review.publishable && review.changed.length > 0) {
    for (const [command, args] of [
        ['git', ['add', '--', ...review.changed.map(change => change.path)]],
        ['git', ['commit', '--message', review.commitMessage]],
        ['git', ['push', 'origin', `HEAD:refs/heads/${HEAD_REF}`]],
    ]) {
        const step = run(command, args)

        if (!step.ok) {
            review.publishable = false
            review.problems.push(`${command} ${args.join(' ')} failed:\n${step.output}`)
            break
        }
    }
}

const comment = outcomeComment(review)
const commented = run('gh', ['pr', 'comment', PR_NUMBER, '--body-file', '-'], {input: comment})
console.log(comment)

if (!commented.ok) {
    console.error(`Could not comment on the PR:\n${commented.output}`)
}

// Marks the run as done; the workflow comments itself when this is missing.
if (outcomeFile) {
    writeFileSync(outcomeFile, `${review.publishable ? 'published' : 'blocked'}${commented.ok ? '' : ', not commented'}`)
}

process.exitCode = review.publishable ? 0 : 1
