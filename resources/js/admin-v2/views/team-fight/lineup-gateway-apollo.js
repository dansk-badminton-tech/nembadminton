import gql from "graphql-tag"
import TeamRoundQuery from "../../../queries/teamRound.graphql"

/* The LineupGateway on Apollo. Each write gets the session's (teamRoundId, scenarioId) and
   uses what its mutation needs; the session refetches the editor's query after each
   operation, so the mutations select little. */
export function createApolloLineupGateway(client) {
    const mutate = (mutation, variables) => client.mutate({mutation, variables}).then(({data}) => data)

    return {
        refetch(teamRoundId, scenarioId) {
            // A network-only read writes the result to the cache, which updates the editor's query.
            return client.query({
                query: TeamRoundQuery,
                variables: {id: teamRoundId, scenarioId},
                fetchPolicy: 'network-only'
            })
        },

        addSquadMember({categoryId, refId, version}) {
            return mutate(gql`
                mutation addSquadMemberByRefId($input: AddSquadMemberByRefIdInput!){
                    addSquadMemberByRefId(input: $input){
                        id
                        refId
                    }
                }
            `, {input: {categoryId: parseInt(categoryId), refId, version}})
                .then(data => data.addSquadMemberByRefId)
        },

        deleteSquadMember({id}) {
            return mutate(gql`
                mutation deleteSquadMember($id: ID!){
                    deleteSquadMember(id: $id){
                        id
                    }
                }
            `, {id})
        },

        addSquadReserve({scenarioId, squadId, reserveRefId, replacedRefId}) {
            return mutate(gql`
                mutation addSquadReserve($squadId: ID!, $scenarioId: ID, $reserveRefId: String!, $replacedRefId: String!){
                    addSquadReserve(squadId: $squadId, scenarioId: $scenarioId, reserveRefId: $reserveRefId, replacedRefId: $replacedRefId){
                        id
                    }
                }
            `, {squadId, scenarioId, reserveRefId, replacedRefId})
        },

        deleteSquadReserve({id}) {
            return mutate(gql`
                mutation deleteSquadReserve($id: ID!){
                    deleteSquadReserve(id: $id){
                        id
                    }
                }
            `, {id})
        },

        createSquad({teamRoundId, input}) {
            return mutate(gql`
                mutation createSquad($input: CreateSquadInput!){
                    createSquad(input: $input){
                        id
                    }
                }
            `, {input: {teamRound: {connect: teamRoundId}, ...input}})
        },

        updateSquad({input}) {
            return mutate(gql`
                mutation updateSquad($input: UpdateSquadInput!){
                    updateSquad(input: $input){
                        id
                    }
                }
            `, {input})
        },

        updatePointsSquad({id, version}) {
            return mutate(gql`
                mutation updatePointsSquad($id: ID!, $version: String){
                    updatePointsSquad(id: $id, version: $version){
                        id
                    }
                }
            `, {id, version})
        },

        moveSquadOrderUp({id}) {
            return mutate(gql`
                mutation moveSquadOrderUp($id: ID!){
                    moveSquadOrderUp(id: $id){
                        id
                    }
                }
            `, {id})
        },

        moveSquadOrderDown({id}) {
            return mutate(gql`
                mutation moveSquadOrderDown($id: ID!){
                    moveSquadOrderDown(id: $id){
                        id
                    }
                }
            `, {id})
        },

        deleteSquad({id}) {
            return mutate(gql`
                mutation deleteSquad($id: ID!){
                    deleteSquad(id: $id){
                        id
                    }
                }
            `, {id})
        },

        // The new Scenario is copied from scenarioId, or from the official Lineup when it is null.
        createScenario({teamRoundId, scenarioId, name}) {
            return mutate(gql`
                mutation CreateScenario($teamRoundId: ID!, $name: String!, $sourceScenarioId: ID) {
                    createScenario(teamRoundId: $teamRoundId, name: $name, sourceScenarioId: $sourceScenarioId) {
                        id
                        name
                        isOfficial
                    }
                }
            `, {teamRoundId, name, sourceScenarioId: scenarioId === null ? null : String(scenarioId)})
                .then(data => data.createScenario)
        },

        promoteScenario({scenarioId}) {
            return mutate(gql`
                mutation PromoteScenario($scenarioId: ID!) {
                    promoteScenario(scenarioId: $scenarioId) {
                        id
                    }
                }
            `, {scenarioId: String(scenarioId)})
        },

        renameScenario({scenarioId, name}) {
            return mutate(gql`
                mutation RenameScenario($scenarioId: ID!, $name: String!) {
                    renameScenario(scenarioId: $scenarioId, name: $name) {
                        id
                        name
                    }
                }
            `, {scenarioId: String(scenarioId), name})
        },

        deleteScenario({scenarioId}) {
            return mutate(gql`
                mutation DeleteScenario($scenarioId: ID!) {
                    deleteScenario(scenarioId: $scenarioId)
                }
            `, {scenarioId: String(scenarioId)})
        },
    }
}
