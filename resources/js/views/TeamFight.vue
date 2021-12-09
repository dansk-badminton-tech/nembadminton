<template>
    <div>
        <b-loading v-model="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
        <b-button :loading="saving" :class="{'is-success': this.savingIcon === 'check'}" :icon-left="savingIcon" @click="saveTeams">Gem</b-button>
<!--        <b-button icon-left="share-alt" @click="publish">Del</b-button>-->
        <!--        <b-button icon-left="bell" @click="notify">Notificer</b-button>-->
        <b-dropdown aria-role="list" class="ml-2">
            <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                <span>Indstillinger</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="validate">
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
        <b-dropdown aria-role="list" class="ml-2">
            <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                <span>Export</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="exportToCSV">
                <b-icon icon="file-export"></b-icon>
                CSV
            </b-dropdown-item>
        </b-dropdown>
        <div class="columns mt-2">
            <div class="column">
                <b-field label="Navn">
                    <b-input v-model="team.name" placeholder="fx. Runde 1"></b-input>
                </b-field>
            </div>
            <div class="column">
                <b-field label="Spilledato">
                    <b-datepicker
                        v-model="gameDate"
                        icon="calendar-alt"
                        locale="da-DK"
                        placeholder="Klik for at vælge dato..."
                        :first-day-of-week="1"
                        trap-focus>
                    </b-datepicker>
                </b-field>
            </div>
            <div class="column">
                <b-field label="Rangliste">
                    <RankingVersionSelect v-model="version" expanded></RankingVersionSelect>
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
        <ValidationStatus :incomplete-team="resolveIncompleteTeam" :invalid-category="resolveInvalidCategory"
                          :invalid-level="resolveInvalidLevel"/>
        <hr/>
        <div class="columns">
            <div class="column is-6">
                <h1 class="title">Spiller</h1>
                <h1 class="subtitle">Søg på spiller og sæt afbud</h1>
                <PlayersListSearch :add-player="addPlayer" :team-id="this.teamFightId" :club-id="team.club.id"
                                   :version="new Date(version)" />
            </div>
            <div class="column is-6">
                <h1 class="title">Holdet</h1>
                <h1 class="subtitle">Træk spillerne rundt ved at drag-and-drop</h1>
                <TeamTable :confirm-delete="deleteTeam"
                           :copy-player="copyPlayer"
                           :delete-player="deletePlayer"
                           :move="move"
                           @end="saveAndValidate"
                           :playing-to-high="playingToHighList"
                           :playing-to-high-in-squad="playingToHighSquadList"
                           :teams="team.squads"
                           :teams-base-validations="validateBasicSquads"
                           :version="new Date(version)"
                           :club-id="team.club.id"/>
                <div v-if="team.squads.length === 0" class="content has-text-grey has-text-centered">
                    <p>
                        <b-icon
                            icon="users"
                            size="is-large">
                        </b-icon>
                    </p>
                    <p>Tilføj dit første hold</p>
                    <div class="buttons">
                        <b-button
                            type="is-primary"
                            @click="addTeam6">
                            Tilføj 6 personers hold
                        </b-button>
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
                </div>
                <div v-if="team.squads.length > 0" class="content has-text-grey has-text-centered">
                    <p>
                        <b-icon
                            icon="users"
                            size="is-large">
                        </b-icon>
                    </p>
                    <p>Tilføj et nyt hold</p>
                    <div class="buttons">
                        <b-button
                            type="is-primary"
                            @click="addTeam6">
                            Tilføj 6 personers hold
                        </b-button>
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
                </div>
            </div>
        </div>
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
import {TeamFightHelper} from "../components/team-fight/teams";
import RankingVersionSelect from "../components/team-fight/RankingVersionSelect";
import ValidationStatus from "./ValidationStatus";
import PlayersListSearch from "./PlayersListSearch";
import {
    containsMen,
    containsWomen,
    isMensDouble,
    isMensSingle,
    isMixDouble,
    isWomenDouble,
    isWomensSingle
} from "../helpers";

export default {
    name: "TeamFight",
    components: {
        PlayersListSearch,
        ValidationStatus,
        RankingVersionSelect,
        TeamTable,
        ValidateTeams,
        PlayerList,
        PlayerSearch,
        Draggable
    },
    props: {
        teamFightId: String
    },
    computed: {
        resolveIncompleteTeam() {
            if (this.validateBasicSquads.length === 0) {
                return null
            }
            return this.validateBasicSquads.find(data => data.missingPlayerInCategory === true || data.spotsFulfilled === false) !== undefined
        },
        resolveInvalidCategory() {
            if (!this.canValidateSquads) {
                return null
            }
            const playersWithoutYouth = this.playingToHighSquadList.filter((playerInfo)=>{return !playerInfo.isYouthPlayer})
            const playersWithoutYouthPartner = playersWithoutYouth.filter((playerInfo)=>{return !playerInfo.hasYouthPlayerPartner})
            return playersWithoutYouthPartner.length > 0
        },
        resolveInvalidLevel() {
            if (!this.canValidateCrossSquads) {
                return null
            }
            const playersWithoutYouth = this.playingToHighList.filter((playerInfo)=>{return !playerInfo.isYouthPlayer})
            return playersWithoutYouth.length > 0
        }
    },
    data() {
        return {
            validateBasicSquads: [],
            playingToHighList: [],
            canValidateCrossSquads: false,
            playingToHighSquadList: [],
            canValidateSquads: false,
            teamCount: 1,
            players: [],
            showShareLink: false,
            saving: false,
            updating: false,
            shareUrl: '',
            gameDate: new Date(),
            version: null,
            savingIcon: 'save',
            team: {
                squads: [],
                club: {}
            }
        }
    },
    apollo: {
        team: {
            query: gql` query team($id: ID!){
                  team(id: $id){
                    id
                    squads{
                        id
                        playerLimit
                        league
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
                                    vintage
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
            fetchPolicy: "no-cache", // Needs to be "no-cache" because of https://github.com/vuejs/vue-apollo/discussions/492
            result({data}) {
                this.gameDate = new Date(data.team.gameDate);
                this.version = data.team.version;
                this.validate()
            }
        }
    },
    mounted() {
        this.$root.$on('playersearch.addMemberToCategory', () => {
            this.saveTeams()
            this.validate()
        })
        this.$root.$on('teamfight.deletedMemberFromCategory', () => {
            this.saveTeams()
            this.validate()
        })
    },
    methods: {
        exportToCSV(){
            this.$apollo.query({
                query: gql`
                    query exportToCSV($teamId: ID!){
                        export(teamId:$teamId)
                    }
                `,
                variables: {
                    teamId: this.teamFightId
                },
                fetchPolicy: "network-only"
            }).then(({data}) => {
                var file_path = data.export;
                var a = document.createElement('A');
                a.href = file_path;
                a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

            }).catch((error) => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke download CSV :(`
                    })
            })
        },
        wrapInTeamAndSquads(squads){
            return omitDeep(squads, ['__typename', 'cancellations']).map((squad) => ({
                name: 'Team X',
                squad: squad
            }))
        },
        updateToRankingList() {
            this.updating = true;
            let version = this.version;
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
                            type: 'is-danger',
                            message: `Kunne ikke opdater points :(`
                        })
                })
                .finally(() => {
                    this.updating = false;
                })
        },
        validateSquads() {
            const teamsClone = JSON.parse(JSON.stringify(this.team));
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateSquads($input: [ValidateTeam!]!){
                          validateSquads(input: $input){
                            name
                            id
                            refId
                            category
                            gender
                            isYouthPlayer
                            hasYouthPlayerPartner
                            belowPlayer {
                                name
                                id
                                refId
                            }
                          }
                        }
                    `,
                    variables: {
                        input: omitDeep(this.wrapInTeamAndSquads(teamsClone.squads), ['league'])
                    }
                })
                .then(({data}) => {
                    this.playingToHighSquadList = data.validateSquads;
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-dagner',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateSquad)`
                        })
                })
        },
        validateCrossSquads() {
            const crossSquads = JSON.parse(JSON.stringify(this.team));
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateCrossSquads($input: [ValidateTeam!]!){
                          validateCrossSquads(input: $input){
                            name
                            id
                            category
                            refId
                            isYouthPlayer
                            belowPlayer {
                                name
                                id
                                refId
                            }
                          }
                        }
                    `,
                    variables: {
                        input: this.wrapInTeamAndSquads(crossSquads.squads)
                    }
                })
                .then(({data}) => {
                    this.playingToHighList = data.validateCrossSquads;
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-dagner',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validate)`
                        })
                })
        },
        saveAndValidate(){
            this.saveTeams()
            this.validate()
        },
        validate() {
            const teamsClone = JSON.parse(JSON.stringify(this.team));
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateBasicSquads($input: [ValidateTeam!]!){
                          validateBasicSquads(input: $input){
                            index
                            missingPlayerInCategory
                            spotsFulfilled
                          }
                        }
                    `,
                    variables: {
                        input: this.wrapInTeamAndSquads(teamsClone.squads)
                    }
                })
                .then(({data}) => {
                    this.validateBasicSquads = data.validateBasicSquads;
                    if (!this.resolveIncompleteTeam) {
                        this.canValidateCrossSquads = true
                        this.canValidateSquads = true
                        this.validateSquads()
                        this.validateCrossSquads();
                    } else {
                        this.canValidateCrossSquads = false
                        this.canValidateSquads = false
                    }
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-dagner',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateSquad)`
                        })
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
            this.$root.$emit('teamfight.deletedMemberFromCategory')
        },
        copyPlayer(category, player) {
            category.players.push(Object.assign({}, player))
            this.saveTeams()
        },
        deleteTeam(team) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette hold ' + (this.team.squads.indexOf(team) + 1) + '?',
                    onConfirm: () => {
                        this.team.squads.splice(this.team.squads.indexOf(team), 1)
                        this.saveTeams()
                    }
                })
        },
        selectClub(id) {
            this.clubId = id
        },
        addedPlayerNotification(squadIndex, category){
            this.$buefy.snackbar.open(
                {
                    duration: 3000,
                    type: 'is-success',
                    queue: false,
                    message: 'Tilføjet til Hold '+(squadIndex+1)+' i '+category
                })
        },
        addPlayer(player) {
            let foundPlace = false;
            outside:
                for (const [index, squad] of this.team.squads.entries()) {
                    for (const category of squad.categories) {
                        if (isWomenDouble(category) && category.players.length < 2 && player.gender === 'K') {
                            this.addedPlayerNotification(index, category.name)
                            category.players.push(player)
                            foundPlace = true;
                            break outside;
                        } else if (isMensDouble(category) && category.players.length < 2 && player.gender === 'M') {
                            this.addedPlayerNotification(index, category.name)
                            category.players.push(player)
                            foundPlace = true;
                            break outside;
                        } else if (isMixDouble(category) && category.players.length < 2) {
                            if (category.players.length === 0) {
                                this.addedPlayerNotification(index, category.name)
                                category.players.push(player)
                                foundPlace = true;
                                break outside;
                            } else if (containsWomen(category) && player.gender === 'M') {
                                this.addedPlayerNotification(index, category.name)
                                category.players.push(player)
                                foundPlace = true;
                                break outside;
                            } else if (containsMen(category) && player.gender === 'K') {
                                this.addedPlayerNotification(index, category.name)
                                category.players.push(player)
                                foundPlace = true;
                                break outside;
                            }
                        } else if (isMensSingle(category) && category.players.length < 1 && player.gender === 'M') {
                            this.addedPlayerNotification(index, category.name)
                            category.players.push(player)
                            foundPlace = true;
                            break outside;
                        } else if (isWomensSingle(category) && category.players.length < 1 && player.gender === 'K') {
                            this.addedPlayerNotification(index, category.name)
                            category.players.push(player)
                            foundPlace = true;
                            break outside;
                        }
                    }
                }
            if (foundPlace === false) {
                this.$buefy.snackbar.open(
                    {
                        duration: 3000,
                        type: 'is-danger',
                        queue: false,
                        message: `Kunne ikke finde en ledig plads på nogle hold`
                    })
            }else{
                this.saveTeams()
            }
        },
        move(index, offset) {
            let teams = this.team.squads.slice()
            let temp = teams[index]
            teams[index] = teams[index + offset]
            teams[index + offset] = temp
            this.team.squads = teams
            this.saveTeams()
        },
        addTeam10() {
            let players = TeamFightHelper.generate10Players()
            players.id = this.teamCount++
            this.team.squads.push(players)
            this.saveTeams()
        },
        addTeam8() {
            let players = TeamFightHelper.generate8Players()
            players.id = this.teamCount++
            this.team.squads.push(players)
            this.saveTeams()
        },
        addTeam6() {
            let players = TeamFightHelper.generate6Players()
            players.id = this.teamCount++
            this.team.squads.push(players)
            this.saveTeams()
        },
        saveTeams() {
            this.saving = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updateTeam($input: UpdateTeamInput!){
                          updateTeam(input: $input){
                            id
                            name
                            gameDate
                            version
                          }
                        }
                    `,
                    variables:
                        {
                            input: {
                                id: this.teamFightId,
                                name: this.team.name,
                                version: this.version,
                                gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                                squads: this.wrapInTeamAndSquads(this.team.squads).map(o => o['squad'])
                            }
                        }
                })
                .then(({data}) => {
                    this.$root.$emit('teamfight.teamSaved')
                    this.saving = false;
                    this.savingIcon = 'check';
                    setTimeout(() => {
                        this.savingIcon = 'save';
                    }, 2000);
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

