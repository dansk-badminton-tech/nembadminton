// The holdrunde journey that guides place themselves on through their `journey` front matter.
export const journeyStages = [
    {key: 'setup', label: 'Opret og klargør'},
    {key: 'cancellations', label: 'Håndtér afbud'},
    {key: 'lineup', label: 'Lav holdopstillingen'},
    {key: 'sharing', label: 'Del holdopstillingen'},
]

const branchLabels = {optional: 'Valgfrit', alternative: 'Alternativ'}

export const journeyRoles = ['step', ...Object.keys(branchLabels), 'troubleshooting']

// Groups guides (already in display order) into the journey overview. Stages without guides are left out.
export function buildGuideOverview(guides) {
    const guidesAt = (stage, roles) => guides.filter(guide => guide.journey?.stage === stage.key && roles.includes(guide.journey.role))

    const stages = journeyStages.map((stage, index) => ({
        ...stage,
        number: index + 1,
        step: guidesAt(stage, ['step'])[0] ?? null,
        branches: guidesAt(stage, Object.keys(branchLabels)).map(guide => ({guide, label: branchLabels[guide.journey.role]})),
    })).filter(stage => stage.step || stage.branches.length > 0)

    return {
        firstStep: stages[0]?.number === 1 ? stages[0].step : null,
        stages,
        troubleshooting: journeyStages.flatMap(stage => guidesAt(stage, ['troubleshooting']).map(guide => ({guide, stageLabel: stage.label}))),
        other: guides.filter(guide => !guide.journey),
    }
}
