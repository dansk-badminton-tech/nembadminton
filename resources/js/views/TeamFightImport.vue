<template>
    <section>
        <b-button tag="router-link"
                  type="is-link"
                  v-bind:to="'/team-fight/'+this.teamFightId+'/edit'">
            Tilbage
        </b-button>
        <div class="m-5"></div>
        <form @submit.prevent>
            <div class="columns">
                <div class="column">
                    <b-field label="Spille dato">
                        <b-input v-model="gameDate" disabled></b-input>
                    </b-field>
                </div>
                <div class="column">
                    <b-field label="Ranglist version">
                        <b-input v-model="version" disabled></b-input>
                    </b-field>
                </div>
            </div>
            <b-field label="Klub">
                <BadmintonPlayerClubs v-model="clubId" @input="resetAll"/>
            </b-field>
            <b-field label="Sæson">
                <b-select v-model="season" expanded placeholder="Vælge sæson">
                    <option value="2020">2020</option>
                    <option value="2021">2021</option>
                </b-select>
            </b-field>
            <b-field label="Hold">
                <BadmintonPlayerTeams v-model="playerTeam" :clubId="clubId" :season="season" @input="resetTeamMatch"/>
            </b-field>
            <b-field label="Kamp">
                <BadmintonPlayerTeamFights v-model="teamFight" :clubId="clubId" :player-team="playerTeam" :season="season" @input="resetTeamMatch"/>
            </b-field>
            <b-button type="submit" @click="fetchPlayers">Hent kamp</b-button>
        </form>
        <b-loading v-model="fetchingTeamMatch"></b-loading>
        <div v-if="badmintonPlayerTeamMatch" class="columns mt-5">
            <div class="column is-half">
                <h1 class="title">{{ badmintonPlayerTeamMatch.home.name }}</h1>
                <b-button :loading="importing" class="is-primary" expanded @click="importTeam('HOME')">Import</b-button>
                <b-table :data="badmintonPlayerTeamMatch.home.squad.categories">
                    <b-table-column v-slot="props" field="name" label="Kategori">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column v-slot="props" field="players" label="Spillere">
                        <p v-for="player in props.row.players">{{ player.name }}</p>
                    </b-table-column>
                </b-table>
            </div>
            <div class="column is-half">
                <h1 class="title">{{ badmintonPlayerTeamMatch.guest.name }}</h1>
                <b-button :loading="importing" class="is-primary" expanded @click="importTeam('GUEST')">Import</b-button>
                <b-table :data="badmintonPlayerTeamMatch.guest.squad.categories">
                    <b-table-column v-slot="props" field="name" label="Kategori">
                        {{ props.row.name }}
                    </b-table-column>
                    <b-table-column v-slot="props" field="players" label="Spillere">
                        <p v-for="player in props.row.players">{{ player.name }}</p>
                    </b-table-column>
                </b-table>
            </div>
        </div>
    </section>
</template>

<script>
import gql from "graphql-tag"
import BadmintonPlayerClubs from "../components/badminton-player/BadmintonPlayerClubs";
import BadmintonPlayerTeams from "../components/badminton-player/BadmintonPlayerTeams";
import BadmintonPlayerTeamFights from "../components/badminton-player/BadmintonPlayerTeamFights";

export default {
    name: "TeamFightImport",
    components: {BadmintonPlayerTeamFights, BadmintonPlayerTeams, BadmintonPlayerClubs},
    props: {
        teamFightId: String
    },
    data() {
        return {
            gameDate: null,
            clubId: null,
            leagueMatchId: null,
            season: null,
            version: null,
            skipPlayers: true,
            importing: false,
            fetchingTeamMatch: false,
            badmintonPlayerTeamMatch: false,
            playerTeam: null,
            teamFight: null
        }
    },
    apollo: {
        team: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
                    version
                    gameDate
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
                date = new Date(data.team.gameDate);
                this.gameDate = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
            }
        }
    },
    methods: {
        fetchPlayers() {
            this.fetchingTeamMatch = true;
            this.$apollo.query(
                {
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
                    variables: {
                        badmintonInput: {
                            clubId: this.clubId,
                            leagueMatchId: this.teamFight.matchId,
                            season: this.season,
                            version: this.version
                        }
                    }
                })
                .then(({data}) => {
                    this.badmintonPlayerTeamMatch = data.badmintonPlayerTeamMatch
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-dagner',
                            message: 'Kunne ikke hente kamp'
                        })
                })
                .finally(() => {
                    this.fetchingTeamMatch = false;
                })

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
                                leagueMatchId: this.teamFight.matchId,
                                season: this.season,
                                version: this.version
                            },
                            side: side
                        },
                    }
                })
                .then(({data}) => {
                    this.resetTeamMatch()
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
        },
        resetTeamMatch() {
            this.badmintonPlayerTeamMatch = false
        },
        resetAll() {
            this.playerTeam = null
            this.teamFight = null
            this.resetTeamMatch()
        }
    }
}
</script>

