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

The `document-feature` workflow (`.github/workflows/document-feature.yml`) runs this skill when a documentation label is added to a PR. It passes the label and the PR number. CI mode follows the process above with these overrides; everything not listed here is unchanged.

- **The label is final.** Skip the classification in step 2 and change no labels. `docs:announcement` means one new Release Announcement and nothing else: change no other file, not even an existing guide or announcement.
- **Nobody answers questions.** Where step 3 would ask, write an `<!-- TODO(agent): <question> -->` marker as described in [WRITING-GUIDE.md](./WRITING-GUIDE.md#open-questions). Never guess a behavior to avoid a marker.
- **Date the announcement with the day of the run** (`date -u +%F`), in its file name and `published`.
- **Validation in step 4:** errors that report an unresolved `TODO(agent)` marker are expected; leave those markers in place. Fix every other validation error and any build failure the draft causes.
- **Publish instead of leaving changes uncommitted.** When the draft is done, run `node scripts/docs-agent/publish-announcement.mjs` exactly once and do not commit or push yourself. The script reruns `yarn docs:validate` and `yarn build` and checks that the only change is one new announcement dated today. If so, it commits `docs: draft release announcement (agent)` to the PR head branch, pushes it, and comments with the changed file and the open questions. Otherwise it pushes nothing and comments with the errors.
- **Report** the script's comment as your final answer, since the run log is the only other place it appears.
