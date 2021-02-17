<template>
    <fragment v-if="!$apollo.loading">
        <h1 class="title">{{ team.name }} - {{ team.club.name1 }}</h1>
        <h2 class="subtitle">Spille dato: {{ team.gameDate }}</h2>
        <b-button
            label="Notifikationer"
            size="is-medium"
            type="is-primary"
            @click="showNotificationPopUp = true"/>

        <b-modal
            v-model="showNotificationPopUp"
            :destroy-on-hide="false"
            aria-label="Notifikation"
            aria-modal
            aria-role="dialog"
            has-modal-card
            trap-focus>
            <template #default="props">
                <CreateNotification></CreateNotification>
            </template>
        </b-modal>
        <b-field label="Søg efter spiller">
            <b-input v-model="searchPlayer"></b-input>
        </b-field>
        <div class="columns is-multiline">
            <TeamTable :search="this.searchPlayer" :teams="this.team.squads" :viewMode="true"/>
        </div>

    </fragment>
</template>

<script>
import gql from 'graphql-tag'
import TeamTable from "./TeamTable";
import CreateNotification from "../components/team-fight/CreateNotification";

export default {
    name: "TeamFightPublic",
    components: {
        CreateNotification,
        TeamTable
    },
    methods: {
        sendNotification() {
            this.loading = true
            this.$apollo.mutate({
                                    mutation: gql`
                                    mutation{
                                        sendNotification
                                    }
                                `
                                })
        }
    },
    data() {
        return {
            searchPlayer: '',
            showNotificationPopUp: false
        }
    },
    props: {
        teamFightId: String,
    },
    apollo: {
        team: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
                    name
                    gameDate
                    squads{
                        id
                        playerLimit
                        categories{
                            category
                            name
                            players{
                                gender
                                id
                                name
                                refId
                                points{
                                    category
                                    points
                                    position
                                }
                            }
                        }
                    }
                    club {
                        id
                        name1
                    }
                  }
                }`,
            fetchPolicy: "network-only",
            variables: function () {
                return {
                    id: this.teamFightId
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
