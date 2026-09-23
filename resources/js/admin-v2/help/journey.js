// The holdrunde journey that guides place themselves on through their `journey` front matter.
export const journeyStages = [
    {key: 'setup', label: 'Opret og klargør'},
    {key: 'cancellations', label: 'Håndtér afbud'},
    {key: 'lineup', label: 'Lav holdopstillingen'},
    {key: 'sharing', label: 'Del holdopstillingen'},
]

export const journeyRoles = ['step', 'optional', 'troubleshooting']

// Groups guides (already in display order) into the journey overview. Stages without guides are left out.
export function buildGuideOverview(guides) {
    const placed = (stage, role) => guides.filter(guide => guide.journey?.stage === stage.key && guide.journey.role === role)

    const stages = journeyStages.map((stage, index) => ({
        ...stage,
        number: index + 1,
        step: placed(stage, 'step')[0] ?? null,
        optional: placed(stage, 'optional'),
    }))

    return {
        stages: stages.filter(stage => stage.step || stage.optional.length > 0),
        troubleshooting: journeyStages.flatMap(stage => placed(stage, 'troubleshooting').map(guide => ({guide, stage: stage.label}))),
        other: guides.filter(guide => !guide.journey),
    }
}
