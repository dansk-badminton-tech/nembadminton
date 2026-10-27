import {reactive} from 'vue'

/* A failed Lineup write. `operation` names the session operation, `cause` is the gateway's error. */
export class LineupEditError extends Error {
    constructor(operation, cause) {
        super(`${operation} failed: ${cause?.message ?? cause}`)
        this.name = 'LineupEditError'
        this.operation = operation
        this.cause = cause
    }
}

/* The one place that writes to the open TeamRound. Only the session knows the editor's
   query key (teamRoundId, scenarioId); each operation refetches that query once, also
   when it failed, so the editor shows what the server has.
   onError receives every LineupEditError, so the editor can show it in one place; the
   operation still rejects for callers that need to react. */
export function createLineupSession({gateway, teamRoundId, scenarioId = null, onError = () => {}}) {
    const session = reactive({
        teamRoundId,
        scenarioId,
        saving: false,
        // Goes up after each refetch, so other views can refresh what depends on the Lineup.
        revision: 0,

        refetch() {
            return refetch()
        },

        // Resolves with the added Squad Member.
        addPlayer({categoryId, refId, version}) {
            return run('addPlayer', () => write('addSquadMember', {categoryId, refId, version}))
        },

        removePlayer(player) {
            return run('removePlayer', () => write('deleteSquadMember', {id: player.id}))
        },

        // Two mutations: the add only runs when the delete succeeded, and the Lineup is refetched once at the end.
        move(player, target) {
            return run('move', async () => {
                await write('deleteSquadMember', {id: player.id})
                await write('addSquadMember', {categoryId: target.categoryId, refId: player.refId, version: target.version})
            })
        },

        addReserve({squadId, reserveRefId, replacedRefId}) {
            return run('addReserve', () => write('addSquadReserve', {squadId, reserveRefId, replacedRefId}))
        },

        removeReserve(reserve) {
            return run('removeReserve', () => write('deleteSquadReserve', {id: reserve.id}))
        },

        createSquad(input) {
            return run('createSquad', () => write('createSquad', {input}))
        },

        // With changeRankingList, the Squad's points are also moved to input.version's Ranking List.
        updateSquad(input, {changeRankingList = false} = {}) {
            return run('updateSquad', async () => {
                await write('updateSquad', {input})
                if (changeRankingList) {
                    await write('updatePointsSquad', {id: input.id, version: input.version})
                }
            })
        },

        moveSquadUp(squad) {
            return run('moveSquadUp', () => write('moveSquadOrderUp', {id: squad.id}))
        },

        moveSquadDown(squad) {
            return run('moveSquadDown', () => write('moveSquadOrderDown', {id: squad.id}))
        },

        deleteSquad(squad) {
            return run('deleteSquad', () => write('deleteSquad', {id: squad.id}))
        },

        // The new Scenario is a copy of the open one, and becomes the open one. Resolves with the new Scenario.
        createScenario(name) {
            return run('createScenario', async () => {
                const scenario = await write('createScenario', {name})
                session.scenarioId = scenario.id
                return scenario
            })
        },

        promoteScenario() {
            return run('promoteScenario', () => write('promoteScenario', {}))
        },

        renameScenario(name) {
            return run('renameScenario', () => write('renameScenario', {name}))
        },

        // Deletes the open Scenario and opens the Official Lineup.
        deleteScenario() {
            return run('deleteScenario', async () => {
                await write('deleteScenario', {})
                session.scenarioId = null
            })
        },

        // The editor's query follows scenarioId, so switching needs no write.
        switchScenario(id) {
            session.scenarioId = id ?? null
        },
    })

    function write(name, args) {
        return gateway[name]({teamRoundId: session.teamRoundId, scenarioId: session.scenarioId, ...args})
    }

    async function refetch() {
        await gateway.refetch(session.teamRoundId, session.scenarioId)
        session.revision++
    }

    async function run(operation, writes) {
        session.saving = true
        let result
        let failure = null
        try {
            result = await writes()
        } catch (cause) {
            failure = new LineupEditError(operation, cause)
        }
        try {
            await refetch()
        } finally {
            session.saving = false
        }
        if (failure) {
            onError(failure)
            throw failure
        }
        return result
    }

    return session
}
