<template>
    <div>
        <b-loading v-model="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
        <b-button :loading="saving" icon-left="save" @click="saveTeams">Gem</b-button>
        <b-button icon-left="share-alt" @click="publish">Del</b-button>
        <b-button icon-left="bell" @click="notify">Notificer</b-button>
        <b-dropdown aria-role="list">
            <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                <span>Tilføj hold</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="addTeam8">
                <b-icon icon="users" size="is-small"></b-icon>
                8 personer
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="addTeam10">
                <b-icon icon="users" size="is-small"></b-icon>
                10 personer
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" has-link>
                <router-link v-bind:to="'/team-fight/'+teamFightId+'/import'">
                    <b-icon icon="users" size="is-small"></b-icon>
                    Import fra BadmintonPlayer
                </router-link>
            </b-dropdown-item>
        </b-dropdown>
        <ValidateTeams ref="validateTeams" :teams="team.squads"/>
        <b-dropdown aria-role="list">
            <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                <span>Indstillinger</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="validTeams">
                <b-icon icon="brain"></b-icon>
                Validere hold
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="updateToRankingList">
                <b-icon icon="brain"></b-icon>
                Update player points
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="deleteTeamFight">
                <b-icon icon="trash"></b-icon>
                Slet holdet
            </b-dropdown-item>
        </b-dropdown>
        <div class="columns mt-2">
            <div class="column">
                <b-field label="Navn">
                    <b-input v-model="team.name" placeholder="fx. Runde 1"></b-input>
                </b-field>
            </div>
            <div class="column">
                <b-field label="Spille dato">
                    <b-datepicker
                        v-model="gameDate"
                        icon="calendar-alt"
                        placeholder="Klik for at vælge dato..."
                        :first-day-of-week="1"
                        trap-focus>
                    </b-datepicker>
                </b-field>
            </div>
            <div class="column">
                <b-field label="Rangliste">
                    <RankingListDatePicker v-model="version"/>
                </b-field>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Klub</label>
                    <div class="control is-clearfix">
                        {{ team.club.name1 }}
                    </div>
                </div>
            </div>
        </div>
        <h1 class="title">Holdet</h1>
        <h1 class="subtitle">Træk spillerne rundt ved at drag-and-drop</h1>
        <div class="columns">
            <div class="column">
                <PlayerSearch :add-player="addPlayer" :club-id="team.club.id" :exclude-players="[]" :version="version"></PlayerSearch>
            </div>
        </div>
        <PlayerList :players="players"></PlayerList>
        <div v-if="team.squads.length === 0" class="content has-text-grey has-text-centered">
            <p>
                <b-icon
                    icon="users"
                    size="is-large">
                </b-icon>
            </p>
            <p>Kom i gang med din næste holdkamp planlægning her</p>
            <b-button
                type="is-primary"
                @click="addTeam8">
                Tilføj 8 personers hold
            </b-button>
            <b-button
                type="is-primary"
                @click="addTeam10">
                Tilføj 10 personers hold
            </b-button>
        </div>
        <draggable :list="team.squads" class="columns is-multiline" handle=".handle">
            <TeamTable :confirm-delete="deleteTeam" :copy-player="copyPlayer" :delete-player="deletePlayer" :move="move"
                       :playing-to-high="playingToHighList" :teams="team.squads" @end="validTeams"/>
        </draggable>
        <b-modal v-model="showShareLink" :width="640" scroll="keep">
            <div class="card">
                <div class="card-content">
                    <div class="content">
                        <p>Alle som har linket kan kun se holdet, ikke rediger. Man behøver ikke at være logget ind for
                            at se holdet.</p>
                        <pre>{{ shareUrl }}</pre>
                    </div>
                </div>
                <footer class="card-footer">
                    <a :href="shareUrl" class="card-footer-item" target="_blank">Vis (Nyt vindue)</a>
                    <a class="card-footer-item" @click.prevent="copyShareLink">Kopier</a>
                    <a class="card-footer-item" @click.prevent="showShareLink = !showShareLink">Luk</a>
                </footer>
            </div>
        </b-modal>
    </div>
</template>

<script>
import PlayerSearch from "../components/search-player/PlayerSearch";
import PlayerList from "./PlayerList";
import Draggable from "vuedraggable"
import gql from "graphql-tag"
import ValidateTeams from "./ValidateTeams";
import TeamTable from "./TeamTable";
import omitDeep from 'omit-deep';
import teams, {TeamFightHelper} from "../components/team-fight/teams";
import RankingListDatePicker from "../components/team-fight/RankingListDatePicker";

export default {
    name: "TeamFight",
    components: {
        RankingListDatePicker,
        TeamTable,
        ValidateTeams,
        PlayerList,
        PlayerSearch,
        Draggable
    },
    props: {
        teamFightId: String
    },
    data() {
        return {
            playingToHighList: [],
            teamCount: 1,
            players: [],
            showShareLink: false,
            saving: false,
            updating: false,
            shareUrl: '',
            gameDate: new Date(),
            version: null,
            team: {
                squads: [],
                club: {}
            }
        }
    },
    apollo: {
        team: {
            query: gql` query ($id: ID!){
                  team(id: $id){
                    id
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
                    name
                    gameDate
                    version
                    club {
                        id
                        name1
                    }
                  }
                }`,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            },
            fetchPolicy: "network-only",
            result({data}) {
                this.gameDate = new Date(data.team.gameDate);
                this.version = new Date(data.team.version);
            }
        }
    },
    methods: {
        updateToRankingList() {
            this.updating = true;
            let version = this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate();
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation ($id: ID!, $version: String!){
                          updatePoints(id: $id, version: $version)
                        }
                    `,
                    variables: {
                        id: this.teamFightId,
                        version: version
                    }
                })
                .then(({data}) => {
                    this.$apollo.queries.team.refresh()
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-success',
                            message: `Points er nu ` + version + ' ranglisten'
                        })
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-dagner',
                            message: `Kunne ikke opdater points :(`
                        })
                })
                .finally(() => {
                    this.updating = false;
                })
        },
        validTeams() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation ($input: UpdateTeamInput!){
                          validate(input: $input){
                            playingToHigh
                            name
                            id
                          }
                        }
                    `,
                    variables: {
                        input: {
                            id: this.teamFightId,
                            name: this.team.name,
                            version: this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate(),
                            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                            squads: omitDeep(this.team.squads, ['__typename'])
                        }
                    }
                })
                .then(({data}) => {
                    this.playingToHighList = data.validate;
                })
        },
        copyShareLink() {
            this.$copyText(this.shareUrl).then((e) => {
                this.$buefy.snackbar.open(`Kopiret til udklipsholder`)
                this.showShareLink = false;
            }, (e) => {
                this.$buefy.snackbar.open(`Kunne ikke kopir til udklipsholder. :(`)
            })
        },
        publish() {
            let getUrl = window.location;
            this.shareUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1] + "/" + this.teamFightId + '/view';
            this.showShareLink = !this.showShareLink
        },
        deletePlayer(category, player) {
            category.players.splice(category.players.indexOf(player), 1)
        },
        copyPlayer(category, player) {
            category.players.push(Object.assign({}, player))
        },
        deleteTeam(team) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette hold ' + (this.team.squads.indexOf(team) + 1) + '?',
                    onConfirm: () => {
                        this.team.squads.splice(this.team.squads.indexOf(team), 1)
                    }
                })
        },
        selectClub(id) {
            this.clubId = id
        },
        addPlayer(player) {
            this.players.push(player);
        },
        move(index, offset) {
            let teams = this.team.squads.slice()
            let temp = teams[index]
            teams[index] = teams[index + offset]
            teams[index + offset] = temp
            this.team.squads = teams
        },
        addTeam10() {
            let players = TeamFightHelper.generate10Players()
            players.id = this.teamCount++
            this.team.squads.push(players)
        },
        addTeam8() {
            let players = TeamFightHelper.generate8Players()
            players.id = this.teamCount++
            this.team.squads.push(players)
        },
        loadTeamFromCache() {
            this.team.squads = JSON.parse(localStorage.getItem('teams'));
        },
        saveTeams() {
            localStorage.setItem('teams', JSON.stringify(this.teams));
            this.saving = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation ($input: UpdateTeamInput!){
                          updateTeam(input: $input){
                            id
                          }
                        }
                    `,
                    variables:
                        {
                            input: {
                                id: this.teamFightId,
                                name: this.team.name,
                                version: this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate(),
                                gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                                squads: omitDeep(this.team.squads, ['__typename'])
                            }
                        }
                })
                .then(({data}) => {
                    this.saving = false;
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-success',
                            message: `Dit hold er gemt`
                        })
                })
                .catch((error) => {
                    this.saving = false;
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-dagner',
                            message: `Kunne ikke gemme dit hold :(`
                        })
                })
        },
        deleteTeamFight() {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette helt holdet?',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation ($id: ID!){
                                        deleteTeam(id: $id){
                                            id
                                        }
                                    }
                                `,
                                variables: {
                                    id: this.teamFightId
                                }
                            }).then(() => {
                            this.$router.push({name: 'team-fight-dashboard'})
                        })
                    }
                })
        },
        notify() {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil notificer spillerne omkring ændringer?<br /><br /><strong>OSB</strong>: Det er kun spiller som har tilmeldt sig notifikationer, der vil modtage dem.',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation($id: ID!){
                                        notify(id: $id)
                                    }
                                `,
                                variables: {
                                    id: this.teamFightId
                                }
                            })
                            .then(({data}) => {
                                this.$buefy.snackbar.open(
                                    {
                                        duration: 2000,
                                        type: 'is-success',
                                        message: `Dine spiller er nu notificeret`
                                    })
                            })
                            .catch((error) => {
                                this.$buefy.snackbar.open(
                                    {
                                        duration: 2000,
                                        type: 'is-dagner',
                                        message: `Kunne ikke notificer spillerne`
                                    })
                            })
                    }
                })
        }
    }
}
</script>

