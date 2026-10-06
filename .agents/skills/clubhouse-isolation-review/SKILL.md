---
name: clubhouse-isolation-review
description: Check that a branch or PR keeps each Clubhouse's data isolated from other Clubhouses, before it is merged.
disable-model-invocation: true
---

Review a change for **leaks**: any path where a User of one Clubhouse reads or changes another Clubhouse's data. The rules are the **Clubhouse isolation** section of `../../../CONTRIBUTING.md`, the single source of truth; this skill applies them in depth, following each change out to every entry point that exposes it. Report only; change no files.

## Process

1. **Pin the change.** With a PR number as the argument, use `gh pr diff <number>`. Without one, use `git diff $(git merge-base master HEAD)`, which covers the branch's commits and uncommitted changes, and list untracked files with `git status --short`. Stop and say so if the change is empty.

2. **Load the rules.** Read the **Clubhouse isolation** section of `CONTRIBUTING.md`, `docs/adr/0003-clubhouse-isolation-per-graphql-field.md`, and the `Clubhouse` entry in `GLOSSARY.md`.

3. **Map the exposure.** List every changed item that can carry Clubhouse data: GraphQL fields, types, inputs and directives in `graphql/`; policies in `app/Policies/` and their registration in `AuthServiceProvider`; models (relations, scopes, appended or visible attributes); resolvers in `app/GraphQL/` and `local-vendor/*/src/`; migrations; middleware. For each, trace through the schema (`graphql/schema.graphql` imports the rest) to every root `Query`, `Mutation` and `Subscription` field that reaches it, including through nested types and the public entry points of rule 4. The step is done when every changed item is mapped to its entry points or marked unreachable from GraphQL, with the reason.

4. **Apply every rule to every mapped entry point.** Confirm each check by reading the policy method or resolver body. A directive name alone doesn't count. Shared data (Members, Clubs, Points, ranking versions) is the one exception and yields no finding. A rule is applied when you can say for each entry point that it passes, or name the finding.

5. **Write each finding** as:
   - **What**: `file:line` and the rule number it breaks.
   - **Leak**: a concrete scenario, e.g. "A User of Clubhouse A calls `updateSquad(id, teamId: <B's team>)` and links Clubhouse B's Team".
   - **Fix**: the smallest change that closes it.
   - **Test**: a GraphQL test in `tests/GraphQL/` (name, setup with a second Clubhouse, expected denial or empty result), in the style of `it_denies_updating_team_in_another_clubhouse` in `tests/GraphQL/TeamsCrudTest.php`. A missing isolation test (rule 6) is a finding of its own.

   Mark each one **confirmed** (you traced the leak end to end) or **suspected** (it depends on something you couldn't verify, which you name). A leak that already exists on `master` and that the change only passes by is **pre-existing**; report it as one line citing issue #249 (`gh issue view 249`) when it is listed there, and in full otherwise.

6. **Report** in the terminal:
   - The first line is `Isolation: no concerns` or `Isolation: N new findings (C confirmed, S suspected), P pre-existing`.
   - Then the findings: new before pre-existing, writes before reads, and confirmed before suspected.
   - Close with **Checked**: one line per entry point that passed (noting any pre-existing finding it carries), so the reader can see what was covered.
