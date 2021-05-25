<template>
    <section>
        <form>
            <b-field label="Klub">
                <BadmintonPlayerClubs v-model="clubId"/>
            </b-field>
            <b-field label="Sæson">
                <b-select v-model="season" expanded placeholder="Vælge sæson">
                    <option value="2020">2020</option>
                    <option value="2021">2021</option>
                </b-select>
            </b-field>
            <b-field label="Hold">
                <BadmintonPlayerTeams v-model="playerTeam" :clubId="clubId" :season="season"/>
            </b-field>
            <b-field label="Kamp">
                <BadmintonPlayerTeamFights v-model="teamFight" :clubId="clubId" :player-team="playerTeam" :season="season"/>
            </b-field>
            <b-button @click="addTeamFight">Tilføj</b-button>
            <p v-for="teamFight in selectedTeamFights">{{ teamFight.teamFight.gameTime }} - {{ teamFight.teamFight.teams.join(' - ') }}</p>
            <b-button @click="badmintonPlayerTeamMatchesImport">Hent</b-button>
            <b-button @click="validate">Validate</b-button>
            <div class="columns is-multiline">
                <div v-for="team in teams" class="column">
                    <h1 class="title">{{ team.name }}</h1>
                    <b-table :data="team.squad.categories">
                        <b-table-column v-slot="props" field="name" label="Kategori">
                            {{ props.row.name }}
                        </b-table-column>
                        <b-table-column v-slot="props" field="players" label="Spillere">
                            <p v-for="player in props.row.players">{{ player.name }}</p>
                        </b-table-column>
                    </b-table>
                </div>
            </div>
        </form>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../components/badminton-player/BadmintonPlayerClubs";
import BadmintonPlayerTeams from "../components/badminton-player/BadmintonPlayerTeams";
import BadmintonPlayerTeamFights from "../components/badminton-player/BadmintonPlayerTeamFights";
import gql from "graphql-tag";
import omitDeep from "omit-deep";

export default {
    name: "CheckTeamFight",
    components: {BadmintonPlayerTeamFights, BadmintonPlayerTeams, BadmintonPlayerClubs},
    data() {
        return {
            clubId: null,
            playerTeam: null,
            season: null,
            teamFight: null,
            selectedTeamFights: [],
            teams: [],
            playingToHigh: []
        }
    },
    methods: {
        badmintonPlayerTeamMatchesImport() {
            this.$apollo.mutate(
                {
                    mutation: gql`mutation ($input: BadmintonPlayerTeamMatchInput!){
                        badmintonPlayerTeamMatchesImport(input: $input){
                            name
                            squad{
                              playerLimit
                              categories{
                                category
                                name
                                players{
                                  refId
                                  name
                                  gender
                                  points{
                                    points
                                    position
                                    category
                                    version
                                  }
                                }
                              }
                            }
                          }
                        }
                    `,
                    variables: {
                        input: {
                            clubId: parseInt(this.clubId),
                            leagueMatchIds: this.selectedTeamFights.map((team) => (team.teamFight.matchId)),
                            season: parseInt(this.season),
                            version: "2020-08-01"
                        }
                    }
                }
            ).then(({data}) => {
                this.teams = data.badmintonPlayerTeamMatchesImport
            })
        },
        validate() {
            this.$apollo.mutate(
                {
                    mutation: gql`mutation validateTeamMatch($input: [ValidateTeam!]!){
                      validateTeamMatch(input: $input){
                        name
                        id
                      }
                    }
                    `,
                    variables: {
                        input: omitDeep(this.teams, ['__typename'])
                    }
                }
            ).then(({data}) => {
                this.playingToHigh = data.validateTeamMatch
            })
        },
        addTeamFight() {
            this.selectedTeamFights.push(
                {
                    clubId: this.clubId,
                    season: this.season,
                    playerTeam: this.playerTeam,
                    teamFight: this.teamFight
                });
        }
    }
}
</script>

<style scoped>

</style>
