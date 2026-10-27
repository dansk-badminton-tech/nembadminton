import {test} from 'node:test'
import assert from 'node:assert/strict'
import {createLineupSession, LineupEditError} from '../../../resources/js/admin-v2/views/team-fight/lineup-session.js'
import {createFakeLineupGateway} from './fake-lineup-gateway.js'

const player = {id: '7', refId: '0101-01'}
const target = {categoryId: '2', version: '2026-08-01'}

test('a move stops after a failed delete', async () => {
    const gateway = createFakeLineupGateway()
    gateway.failOn('deleteSquadMember')
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: null})

    await assert.rejects(session.move(player, target))

    assert.deepEqual(gateway.names(), ['deleteSquadMember', 'refetch'])
})

test('a move deletes, adds and refetches once', async () => {
    const gateway = createFakeLineupGateway()
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: null})

    await session.move(player, target)

    assert.deepEqual(gateway.names(), ['deleteSquadMember', 'addSquadMember', 'refetch'])
    assert.deepEqual(gateway.calls[1].args, {teamRoundId: '10', scenarioId: null, categoryId: '2', refId: '0101-01', version: '2026-08-01'})
})

test('a failed operation still refetches, then rejects with a LineupEditError', async () => {
    const gateway = createFakeLineupGateway()
    gateway.failOn('addSquadMember')
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: null})

    const pending = session.addPlayer({categoryId: '2', refId: '0101-01', version: null})
    assert.equal(session.saving, true)

    await assert.rejects(pending, (error) => {
        assert.ok(error instanceof LineupEditError)
        assert.equal(error.operation, 'addPlayer')
        assert.equal(error.cause.message, 'addSquadMember failed')
        return true
    })
    assert.deepEqual(gateway.names(), ['addSquadMember', 'refetch'])
    assert.equal(session.saving, false)
})

test('a failed operation is reported to onError before it rejects', async () => {
    const gateway = createFakeLineupGateway()
    gateway.failOn('deleteSquad')
    const reported = []
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: null, onError: error => reported.push(error)})

    await assert.rejects(session.deleteSquad({id: '3'}), error => error === reported[0])
    await session.removePlayer({id: '7'})

    assert.equal(reported.length, 1)
    assert.equal(reported[0].operation, 'deleteSquad')
})

test('after a Scenario switch, every write and refetch uses the switched key', async () => {
    const gateway = createFakeLineupGateway()
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: null})

    session.switchScenario('5')
    await session.addPlayer({categoryId: '2', refId: '0101-01', version: null})
    await session.removePlayer({id: '7'})
    await session.move(player, target)
    await session.addReserve({squadId: '3', reserveRefId: '0202-02', replacedRefId: '0101-01'})
    await session.removeReserve({id: '4'})
    await session.createSquad({playerLimit: 4})
    await session.updateSquad({id: '3', name: 'Hold 1'})
    await session.moveSquadUp({id: '3'})
    await session.moveSquadDown({id: '3'})
    await session.deleteSquad({id: '3'})
    await session.promoteScenario()
    await session.renameScenario('Plan B')

    assert.equal(gateway.names().filter(name => name === 'refetch').length, 12)
    for (const call of gateway.calls) {
        assert.equal(call.args.teamRoundId, '10', call.name)
        assert.equal(call.args.scenarioId, '5', call.name)
    }
})

test('a created Scenario is copied from the open one and becomes the open one', async () => {
    const gateway = createFakeLineupGateway({results: {createScenario: {id: '6', name: 'Plan B'}}})
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: '5'})

    const scenario = await session.createScenario('Plan B')

    assert.equal(scenario.name, 'Plan B')
    assert.equal(session.scenarioId, '6')
    assert.deepEqual(gateway.calls, [
        {name: 'createScenario', args: {teamRoundId: '10', scenarioId: '5', name: 'Plan B'}},
        {name: 'refetch', args: {teamRoundId: '10', scenarioId: '6'}},
    ])
})

test('deleting the open Scenario opens the official one', async () => {
    const gateway = createFakeLineupGateway()
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: '5'})

    await session.deleteScenario()

    assert.equal(session.scenarioId, null)
    assert.deepEqual(gateway.calls, [
        {name: 'deleteScenario', args: {teamRoundId: '10', scenarioId: '5'}},
        {name: 'refetch', args: {teamRoundId: '10', scenarioId: null}},
    ])
})

test('a Scenario that fails to delete stays open', async () => {
    const gateway = createFakeLineupGateway()
    gateway.failOn('deleteScenario')
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: '5'})

    await assert.rejects(session.deleteScenario())

    assert.equal(session.scenarioId, '5')
    assert.deepEqual(gateway.calls[1], {name: 'refetch', args: {teamRoundId: '10', scenarioId: '5'}})
})

test('revision goes up after each refetch', async () => {
    const gateway = createFakeLineupGateway()
    gateway.failOn('deleteSquad')
    const session = createLineupSession({gateway, teamRoundId: '10', scenarioId: null})
    assert.equal(session.revision, 0)

    await session.refetch()
    assert.equal(session.revision, 1)

    await session.removePlayer({id: '7'})
    assert.equal(session.revision, 2)

    await assert.rejects(session.deleteSquad({id: '3'}))
    assert.equal(session.revision, 3)
})
