/* A LineupGateway that records every call and can be told to fail a named one. */
const WRITES = [
    'addSquadMember',
    'deleteSquadMember',
    'addSquadReserve',
    'deleteSquadReserve',
    'createSquad',
    'updateSquad',
    'updatePointsSquad',
    'moveSquadOrderUp',
    'moveSquadOrderDown',
    'deleteSquad',
    'createScenario',
    'promoteScenario',
    'renameScenario',
    'deleteScenario',
]

export function createFakeLineupGateway({results = {}} = {}) {
    const calls = []
    const failing = new Set()

    const gateway = {
        calls,
        failOn(operation) {
            failing.add(operation)
        },
        names() {
            return calls.map(call => call.name)
        },
        async refetch(teamRoundId, scenarioId) {
            calls.push({name: 'refetch', args: {teamRoundId, scenarioId}})
        }
    }

    for (const name of WRITES) {
        gateway[name] = async (args) => {
            calls.push({name, args})
            if (failing.has(name)) {
                throw new Error(`${name} failed`)
            }
            return results[name]
        }
    }

    return gateway
}
