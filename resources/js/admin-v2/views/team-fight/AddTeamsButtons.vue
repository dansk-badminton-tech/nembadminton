<template>
    <div class="content has-text-grey has-text-centered">
        <p>
            <b-icon
                icon="account-group"
                size="is-large">
            </b-icon>
        </p>
        <p>Tilføj et nyt hold</p>
        <div class="buttons is-centered">
            <b-button
                :loading="loading"
                type="is-primary"
                @click="addSquad6">
                9-kamps hold
            </b-button>
            <b-button
                :loading="loading"
                type="is-primary"
                @click="addSquad8">
                11-kamps hold
            </b-button>
            <b-button
                :loading="loading"
                type="is-primary"
                @click="addSquad10">
                13-kamps hold
            </b-button>
        </div>
    </div>
</template>

<script>
import gql from "graphql-tag";
import {TeamFightHelper} from "./teams";
import TeamQuery from "../../../queries/team.graphql";

export default {
    name: "AddTeamsButtons",
    props: ['teamId'],
    data() {
        return {
            loading: false
        }
    },
    methods: {
        addSquad(team){
            this.loading = true
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation createSquad($input: CreateSquadInput!){
                            createSquad(input: $input){
                                id
                                league
                                playerLimit
                                order
                                categories {
                                    id
                                    category
                                    name
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            ...{
                                team: {
                                    connect: this.teamId
                                }
                            },
                            ...team
                        }
                    },
                    refetchQueries: [
                        {query: TeamQuery, variables: {id: this.teamId}}
                    ],
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
        addSquad10() {
            this.addSquad(TeamFightHelper.generateSquadWith10Players())
        },
        addSquad8() {
            this.addSquad(TeamFightHelper.generateSquadWith8Players())
        },
        addSquad6() {
            this.addSquad(TeamFightHelper.generateSquadWith6Players())
        },
    }
}
</script>

<style scoped>

</style>
