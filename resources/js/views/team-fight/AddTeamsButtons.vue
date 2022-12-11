<template>
    <div class="content has-text-grey has-text-centered">
        <p>
            <b-icon
                icon="users"
                size="is-large">
            </b-icon>
        </p>
        <p>Tilføj et nyt hold</p>
        <div class="buttons is-centered">
            <b-button
                :loading="loading"
                type="is-primary"
                @click="addTeam6">
                9-kamps hold
            </b-button>
            <b-button
                :loading="loading"
                type="is-primary"
                @click="addTeam8">
                11-kamps hold
            </b-button>
            <b-button
                :loading="loading"
                type="is-primary"
                @click="addTeam10">
                13-kamps hold
            </b-button>
        </div>
    </div>
</template>

<script>
import gql from "graphql-tag";
import {TeamFightHelper} from "../../components/team-fight/teams";
import TeamQuery from "../../queries/team.graphql";

export default {
    name: "AddTeamsButtons",
    props: ['teamId', 'nextOrder'],
    data() {
        return {
            loading: false
        }
    },
    methods: {
        addTeam(team){
            this.loading = true
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation createSquad($input: CreateSquadInput!){
                            createSquad(input: $input){
                                id
                                league
                                playerLimit
                                categories {
                                    id
                                    category
                                    name
                                    players {
                                        id
                                        gender
                                        name
                                        refId
                                        points{
                                            category
                                            points
                                            position
                                            vintage
                                        }
                                    }
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            ...{
                                team: {
                                    connect: this.teamId
                                },
                                order: this.nextOrder
                            },
                            ...team
                        }
                    },
                    update: (store, { data : { createSquad } }) => {
                        let variables = {id: this.teamId};
                        let data = store.readQuery({ query: TeamQuery, variables: variables })
                        data.team.squads.push(createSquad)
                        store.writeQuery({ query: TeamQuery, data, variables })
                    },
                }).then(() => {
                this.$emit('team-added')
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke oprette holdet :(`
                    })
            }).finally(() => {
                this.loading = false
            })
        },
        addTeam10() {
            this.addTeam(TeamFightHelper.generateSquadWith10Players())
        },
        addTeam8() {
            this.addTeam(TeamFightHelper.generateSquadWith8Players())
        },
        addTeam6() {
            this.addTeam(TeamFightHelper.generateSquadWith6Players())
        },
    }
}
</script>

<style scoped>

</style>
