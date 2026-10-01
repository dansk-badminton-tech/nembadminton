# CI mode

The `Document feature` GitHub Actions workflow runs this skill on a pull request. Its prompt gives you the PR number, the documentation label, and, for a follow-up, the text of a `/docs` comment. The PR head branch is checked out. No one answers questions during the run. These rules replace the matching steps in [SKILL.md](./SKILL.md); the other steps apply unchanged.

- **Evidence (step 1):** read the PR, its linked issue, and its comments with `gh`. Compare against the merge base with `origin/<base branch>`.
- **The given label is final (step 2):** produce what step 2 requires for that label, and leave the PR's labels as they are.
- **Existing drafts:** the branch may already contain Help documents from earlier runs. Read them and the commits that added them (`git log <merge base>..HEAD -- resources/help`). Update them instead of creating duplicates, and add only what the label requires but is missing. Keep every existing file; list any file that no longer fits the label in the summary comment.
- **Open questions become markers (step 3):** write only what the evidence map backs up. Put each point you cannot verify at the place it belongs as `<!-- TODO(agent): <question> -->`, one question per marker. `yarn docs:validate` reports every marker, so the PR cannot merge until each one is resolved.
- **`/docs` follow-ups:** apply the comment's answers to the existing drafts and remove the markers they resolve. `/docs refresh` means rebuilding the evidence map against the current branch and updating the drafts to match.
- **Announcement date:** for a new Release Announcement, use the date of the run (`date +%F`) for `published` and the filename. Keep the date of an existing draft unless the `/docs` comment asks to change it.
- **Validation (step 4):** run `yarn docs:validate`, then `yarn build`. Errors that only report open agent questions are expected. On any other validation error or a build failure, stop before committing and post a PR comment with the error output.
- **Commit and push (step 5):** when the drafts changed, stage only files under `resources/help/` and make one commit: `docs: draft user guide (agent)` for `docs:guide`, `docs: draft release announcement (agent)` for `docs:announcement`, or `docs: apply /docs comment (agent)` for a follow-up. Push it to the PR head branch.
- **Report (step 6):** post one PR comment with `gh pr comment` listing the changed files, every open question (the same text as its marker), and any files that no longer fit the label. End it with how to answer: a `/docs <answers>` comment.
