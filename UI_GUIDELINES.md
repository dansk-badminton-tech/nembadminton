# UI guidelines

Rules for building Nembadminton's user interface. Each rule comes from a mistake seen in a real change; add a rule here when a new mistake repeats, together with the reason behind it.

## Follow the existing UI

The frontend uses [Bulma](https://bulma.io/) for CSS and [Buefy](https://buefy.org/) for components. Pick the first that fits:

1. A Buefy component.
2. Bulma classes and helpers.
3. Custom CSS, only when neither covers the need.

For layout and wording, copy the closest existing page so a new page looks and reads like its neighbours.

## Danish text

Everything a user reads is Danish. Show domain concepts by their Danish names, the terms in parentheses in [`GLOSSARY.md`](GLOSSARY.md), and keep code names and English domain terms (such as `TeamRound` for holdrunde) in the code. An English word belongs in the UI only where Danish badminton players say it in English themselves.

## Short help text

Help text is one short sentence that says what something is or does. This covers tooltips, field help, warnings, notifications and empty states. Long explanations clutter the UI, so they live in a User Guide in Help.

When a second sentence feels needed, the explanation belongs in a User Guide. Add one **Læs mere** link after the sentence when the user must know a rule or follow several steps to act on it. Point it at the User Guide, or at the section of it that covers this case, whichever fits.
