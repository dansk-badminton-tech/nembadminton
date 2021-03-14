<template>
    <section>
        <b-button tag="router-link"
                  type="is-link"
                  v-bind:to="'/team-fight/'+this.teamFightId+'/edit'">
            Tilbage
        </b-button>
        <div class="m-5"></div>
        <form @submit.prevent>
            <b-field>
                <b-select v-model="clubId" placeholder="Select a name">
                    <option
                        v-for="option in badmintonPlayerClubs"
                        :value="option.id"
                        :key="option.id">
                        {{ option.name }}
                    </option>
                </b-select>
            </b-field>
            <b-field label="Kamp Id">
                <b-input v-model="leagueMatchId"></b-input>
            </b-field>
            <b-field label="Sæson">
                <b-input v-model="season"></b-input>
            </b-field>
            <b-field label="Ranglist version">
                <b-input disabled v-model="version"></b-input>
            </b-field>
            <b-button type="submit" @click="fetchPlayers">Test</b-button>
        </form>
        <div v-if="!skipPlayers && !$apollo.loading">
            <div>{{ badmintonPlayerTeamMatch.home.name }}
                <b-button :loading="importing" @click="importTeam('HOME')">Import</b-button>
            </div>
            <div>{{ badmintonPlayerTeamMatch.guest.name }}
                <b-button :loading="importing" @click="importTeam('GUEST')">Import</b-button>
            </div>
        </div>
    </section>
</template>

<script>
import gql from "graphql-tag"

export default {
    name: "TeamFightImport",
    props: {
        teamFightId: String
    },
    data() {
        return {
            clubId: null,
            leagueMatchId: null,
            season: "2020",
            version: null,
            skipPlayers: true,
            importing: false
        }
    },
    apollo: {
        team: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
                    version
                  }
                }`,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            },
            fetchPolicy: "network-only",
            result({data}) {
                let date = new Date(data.team.version);
                this.version = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
            }
        },
        badmintonPlayerClubs: {
            query: gql`
                            query {
                             badmintonPlayerClubs{
                                id
                                name
                              }
                            }
                           `
        }
        ,
        badmintonPlayerTeamMatch: {
            query: gql`
                            query badmintonPlayerTeamMatch($badmintonInput: BadmintonPlayerTeamMatchInput) {
                              badmintonPlayerTeamMatch(input: $badmintonInput) {
                                guest {
                                  ...matcheClub
                                }
                                home{
                                  ...matcheClub
                                }
                              }
                            }
                             fragment matcheClub on ImportTeam {
                                  name
                                  squad {
                                    playerLimit
                                    categories {
                                      category
                                      name
                                      players {
                                        name
                                      }
                                    }
                                  }
                                }`,
            variables() {
                return {
                    badmintonInput: {
                        "clubId": this.clubId,
                        "leagueMatchId": this.leagueMatchId,
                        "season": this.season,
                        "version": this.version
                    }
                }
            },
            skip() {
                return this.skipPlayers
            }
        }
    },
    methods: {
        fetchPlayers() {
            this.skipPlayers = false;
            this.$apollo.queries.badmintonPlayerTeamMatch.refresh()
        },
        importTeam(side) {
            this.importing = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation badmintonPlayerTeamMatchImport($importInput: TeamMatchImportInput) {
                          badmintonPlayerTeamMatchImport(input: $importInput)
                        }`,
                    variables: {
                        importInput: {
                            team: this.teamFightId,
                            badmintonPlayerTeamMatch: {
                                clubId: this.clubId,
                                leagueMatchId: this.leagueMatchId,
                                season: this.season,
                                version: this.version
                            },
                            side: side
                        },
                    }
                })
                .then(({data}) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-success',
                            message: 'Importering færdig'
                        })
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-dagner',
                            message: 'Kunne ikke importer'
                        })
                })
                .finally(() => {
                    this.importing = false;
                })
        }
    }
}
</script>

<style scoped>

</style>
