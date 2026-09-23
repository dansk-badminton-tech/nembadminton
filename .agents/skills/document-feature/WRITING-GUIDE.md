# Help writing guide

Write for people using Nembadminton, in plain Danish. Use the exact labels readers see in the interface and canonical terms from `CONTEXT.md`. Explain outcomes and actions; source code, internal architecture, issue numbers, and implementation details belong in developer artifacts.

## User Guides

Store User Guides in `resources/help/guides/<slug>.md`. Use a short, stable, lowercase slug with hyphens.

```md
---
title: Opret en holdrunde
summary: Sådan opretter og planlægger du en holdrunde.
order: 10
---

Describe the outcome, prerequisites when they matter, and actionable steps. Use only headings that help this workflow; omit empty template sections.
```

Update a guide when documented behavior changes. Create a guide only when no current guide owns the workflow.

### Place the guide in the journey

The Vejledninger overview presents guides as the holdrunde journey. A guide joins it through its own `journey` front matter; no other file changes:

```yaml
journey:
  stage: lineup
  role: optional
```

- `stage` is the point in the journey: `setup` (Opret og klargør), `cancellations` (Håndtér afbud), `lineup` (Lav holdopstillingen), or `sharing` (Del holdopstillingen).
- `role` is `step` for the one guide that owns the stage's normal step, `optional` for an extra branch the reader may take at that point, `alternative` for a branch that replaces the normal step, or `troubleshooting` for a way past a problem that arises there.

Omit `journey` for a guide outside the holdrunde journey; it is listed under "Andre vejledninger". `order` sorts guides that share a stage and role. `yarn docs:validate` rejects unknown stages and roles, and a second `step` guide on the same stage.

## Release Announcements

Store Release Announcements in `resources/help/news/<YYYY-MM-DD>-<slug>.md`. Use the intended deployment date. If that date is unknown, ask for it.

```md
---
title: Ny måde at oprette holdrunder på
summary: Det er blevet nemmere at planlægge en holdrunde.
published: 2026-09-21
guide: opret-en-holdrunde
---

Explain what changed and the benefit to the user. Add `guide` only when the named User Guide exists; the application renders the guide link.
```

Announcements are historical records. Correct factual or language errors, but represent later behavior changes with a new announcement.

## Markdown

Use headings, paragraphs, lists, links, emphasis, code, tables, and blockquotes. Raw HTML is rendered as text. Start body headings at `##` because the page renders the document title as its `h1`.

Write descriptive link text. Link to public sources when they are necessary to complete the task. Screenshots are outside the initial documentation workflow.
