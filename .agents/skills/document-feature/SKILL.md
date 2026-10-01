---
name: document-feature
description: Draft the Danish user documentation for a feature that is ready to merge.
disable-model-invocation: true
---

Document the feature on the current branch. The output is a reviewed draft in the working tree, never a commit.

## Process

1. Build an **evidence map**. Read the current PR and its linked issue and comments when they exist. Find the merge base with the PR base branch, then inspect every committed, staged, unstaged, and untracked change since it. Read relevant tests, user-visible routes and labels, existing Help documents, `CONTEXT.md`, and applicable ADRs. This step is complete when every changed user-visible behavior is accounted for and every instruction you may write has a source.

2. Classify the documentation impact:

   - `docs:guide`: a user workflow is introduced or changed enough to need durable instructions. Update the existing User Guide when one covers the workflow; otherwise create one. Create a Release Announcement too.
   - `docs:announcement`: users should know about the change, but it needs no durable instructions. Create a Release Announcement only.
   - `docs:not-needed`: no user-visible behavior changed. Write no placeholder content.

   Report the classification and its user-impact rationale. If a PR exists and its documentation label must change, ask for confirmation before applying exactly one documentation label. If no PR exists, report the expected label without trying to create one.

3. For `docs:guide` or `docs:announcement`, read [WRITING-GUIDE.md](./WRITING-GUIDE.md) and draft the required Danish content. Treat existing guides as current truth and announcements as history. Ask the user about behavior that the evidence map cannot verify; write only once each instruction is grounded.

4. Run `yarn docs:validate`, then run `yarn build`. Resolve every documentation error and any build failure caused by the draft. Existing unrelated failures should be reported with evidence rather than hidden.

5. Review the final diff. Check that it contains the smallest documentation change that fully explains the user impact, uses canonical terms from `CONTEXT.md`, and contains no implementation details. Leave all changes uncommitted.

6. Report the classification, changed Help files, validation results, and any remaining questions. For `docs:not-needed`, report only the rationale, label state, and that no documentation files were created.

## CI mode

The `Document feature` GitHub Actions workflow runs this skill on a pull request. Its prompt gives you the PR number, the documentation label, and, for a follow-up, the text of a `/docs` comment. The PR head branch is checked out. No one answers questions during the run. In CI mode these rules replace the matching steps above; the rest of the process is unchanged.

- **Evidence (step 1):** read the PR, its linked issue, and its comments with `gh`. Compare against the merge base with `origin/<base branch>`.
- **The label is final (step 2):** do not classify and do not change labels. `docs:guide` means a User Guide (updated or new) plus a Release Announcement. `docs:announcement` means a Release Announcement only.
- **Existing drafts:** the branch may already contain Help documents from earlier runs. Read them and the commits that added them (`git log <merge base>..HEAD -- resources/help`). Update them instead of creating duplicates, and add only what the label requires but is missing. Never delete a file. List any file that no longer fits the label in the summary comment.
- **Open questions instead of asking (step 3):** write only what the evidence map backs up. Put each point you cannot verify at the place it belongs as `<!-- TODO(agent): <question> -->`, one question per marker. `yarn docs:validate` reports every marker, so the PR cannot merge until each one is resolved.
- **`/docs` follow-ups:** apply the comment's answers to the existing drafts and remove the markers they resolve. `/docs refresh` means rebuilding the evidence map against the current branch and updating the drafts to match.
- **Announcement date:** for a new Release Announcement, use the date of the run (`date +%F`) for `published` and the filename. Keep the date of an existing draft unless the `/docs` comment asks to change it.
- **Validation (step 4):** run `yarn docs:validate`, then `yarn build`. Errors that only report open agent questions are expected. On any other validation error or a build failure, commit and push nothing; post a PR comment with the error output and stop.
- **Commit and push (step 5):** stage only files under `resources/help/`. Make one commit: `docs: draft user guide (agent)` for `docs:guide`, `docs: draft release announcement (agent)` for `docs:announcement`, or `docs: apply /docs comment (agent)` for a follow-up. Push it to the PR head branch. If nothing changed, do not commit.
- **Report (step 6):** post one PR comment with `gh pr comment` listing the changed files, every open question (the same text as its marker), and any files that no longer fit the label. End it with how to answer: a `/docs <answers>` comment.
