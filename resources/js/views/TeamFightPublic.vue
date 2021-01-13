<template>
    <section v-if="!$apollo.loading" class="section">
        <h1 class="is-size-1">{{ team.name }}</h1>
        <p>Spille dato: {{ team.gameDate }}</p>
        <b-field label="Søg efter spiller">
            <b-input v-model="searchPlayer"></b-input>
        </b-field>
        <div class="columns is-multiline">
            <TeamTable :search="this.searchPlayer" :teams="this.team.squads" :viewMode="true"/>
        </div>
    </section>
</template>

<script>
import gql from 'graphql-tag'
import TeamTable from "./TeamTable";

export default {
    name: "TeamFightPublic",
    components: {
        TeamTable
    },
    data() {
        return {
            searchPlayer: ''
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
