<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Dashboard
        </hero-bar>
        <section class="section is-main-section">
            <b-loading v-model="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
            <b-tooltip label="Auto-save er slået til. Auto-save sker KUN når der sker ændringer på holdet"
                       position="is-bottom">
                <b-button :loading="saving" :class="{'is-success': this.savingIcon === 'check'}" :icon-left="savingIcon"
                          @click="saveTeams">Gem
                </b-button>
            </b-tooltip>
            <!--        <b-button icon-left="bell" @click="notify">Notificer</b-button>-->
            <b-dropdown aria-role="list" class="ml-2">
                <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                    <span>Export</span>
                    <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                </button>
                <b-dropdown-item aria-role="listitem" @click="exportToCSV">
                    <b-icon icon="file-export"></b-icon>
                    CSV
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="showLinkSharing = true">
                    <b-icon icon="share"></b-icon>
                    <share-link-modal v-model="showLinkSharing" :team-id="teamFightId"></share-link-modal>
                </b-dropdown-item>
            </b-dropdown>
            <b-dropdown aria-role="list" class="ml-2">
                <button slot="trigger" slot-scope="{ active }" class="button is-primary">
                    <span>Indstillinger</span>
                    <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                </button>
                <b-dropdown-item aria-role="listitem" @click="validateWithSnackbar">
                    <b-icon icon="brain"></b-icon>
                    Validere hold
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="deactivateIncompleteCheck">
                    <b-tooltip label="Kan bruges hvis du ikke kan stille et fuld hold">
                        <b-icon icon="cancel"></b-icon>
                        {{ignoreIncompleteTeam ? 'Aktiver' : 'Deaktiver'}} "Fuldendt hold" check
                    </b-tooltip>
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="updateToRankingList">
                    <b-tooltip label="Opdater spillernes point med den valgte rangliste.">
                        <b-icon icon="update"></b-icon>
                        Opdater spiller point
                    </b-tooltip>
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="copyTeamFight">
                    <b-icon icon="content-copy"></b-icon>
                    Kopier hele holdet
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="deleteTeamFight">
                    <b-icon icon="trash-can"></b-icon>
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
                    <b-field label="Spilledato">
                        <b-datepicker
                            v-model="gameDate"
                            icon="calendar"
                            locale="da-DK"
                            placeholder="Klik for at vælge dato..."
                            :first-day-of-week="1"
                            trap-focus>
                        </b-datepicker>
                    </b-field>
                </div>
                <div class="column">
                    <b-field label="Rangliste">
                        <RankingVersionSelect @focus="oldVersion = version" v-model="version"
                                              @change="confirmChangeOfRankingList" expanded></RankingVersionSelect>
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
                    <h1 class="title">Søg på spiller</h1>
                    <h1 class="subtitle">{{
                            hasMultipleClubs
                            ? 'Klubber:'
                            : 'Klub:'
                        }} {{ clubsNames }}
                        <router-link class="is-size-6" :to="{name: 'my-clubs'}">(tilføj extra klub)</router-link>
                    </h1>
                    <PlayersListSearch :loading="saving" :add-player="addPlayerToNextCategory" :team-id="this.teamFightId" :club-id="team.club.id"
                                       :version="new Date(version)"/>
                </div>
                <div class="column is-6 container">
                    <h1 class="title">Holdet</h1>
                    <h1 class="subtitle">Træk spillerne rundt ved drag-and-drop</h1>
                    <TeamTable :confirm-delete="deleteTeam"
                               :delete-player="deletePlayerFromCategory"
                               :add-player="addPlayer"
                               :update-squad="updateSquad"
                               :move-squad-order-down="moveSquadOrderDown"
                               :move-squad-order-up="moveSquadOrderUp"
                               :player-move="playerMove"
                               :playing-to-high="playingToHighList"
                               :playing-to-high-in-squad="playingToHighSquadList"
                               :squads="team.squads"
                               :teams-base-validations="validateBasicSquads"
                               :version="new Date(version)"
                               :club-id="team.club.id"
                               :loading="saving"
                    />
                    <hr>
                    <AddTeamsButtons :team-id="teamFightId"/>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Draggable from "vuedraggable"
import gql from "graphql-tag"
import {
    containsMen,
    containsWomen,
    isMensDouble,
    isMensSingle,
    isMixDouble,
    isWomenDouble,
    isWomensSingle
} from "../../helpers";
import TeamQuery from "../../../queries/team.graphql"
import {hasInvalidCategory, hasInvalidLevel, wrapInTeamAndSquads, wrapSquadsInTeamWithoutLeague} from "./helper";
import ME from "../../../queries/me.gql";
import AddTeamsButtons from "./AddTeamsButtons.vue";
import ShareLinkModal from "./ShareLinkModal.vue";
import PlayersListSearch from "./PlayersListSearch.vue";
import ValidationStatus from "./ValidationStatus.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import TeamTable from "./TeamTable.vue";
import ValidateTeams from "./ValidateTeams.vue";
import PlayerList from "./PlayerList.vue";
import PlayerSearch from "../common/PlayerSearch.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

export default {
    name: "TeamFight",
    components: {
        HeroBar,
        TitleBar,
        AddTeamsButtons,
        ShareLinkModal,
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
        hasMultipleClubs() {
            return this.me?.clubs.length > 1;
        },
        clubsNames() {
            return this.me?.clubs.map((club) => {
                return club.name1
            }).join(', ')
        },
        resolveIncompleteTeam() {
            if(this.ignoreIncompleteTeam){
                return null
            }
            if (this.validateBasicSquads.length === 0) {
                return null
            }
            return this.validateBasicSquads.find(data => data.missingPlayerInCategory === true || data.spotsFulfilled === false) !== undefined
        },
        resolveInvalidCategory() {
            if (!this.canValidateSquads) {
                return null
            }
            return hasInvalidCategory(this.playingToHighSquadList)
        },
        resolveInvalidLevel() {
            if (!this.canValidateCrossSquads) {
                return null
            }
            return hasInvalidLevel(this.playingToHighList)
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Holdkamp'],
            validateBasicSquads: [],
            playingToHighList: [],
            canValidateCrossSquads: false,
            playingToHighSquadList: [],
            canValidateSquads: false,
            teamCount: 1,
            players: [],
            saving: false,
            updating: false,
            gameDate: new Date(),
            version: null,
            oldVersion: null,
            savingIcon: 'content-save',
            showLinkSharing: false,
            team: {
                squads: [],
                club: {}
            },
            ignoreIncompleteTeam: false
        }
    },
    apollo: {
        me: {
            query: ME
        },
        team: {
            query: TeamQuery,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            },
            result({data}) {
                this.gameDate = new Date(data.team.gameDate);
                this.version = data.team.version;
                this.validate()
            }
        }
    },
    methods: {
        deactivateIncompleteCheck(){
            this.ignoreIncompleteTeam = !this.ignoreIncompleteTeam
            this.validate()
        },
        playerMove(event, player, sourceSquad, sourceCategory, targetSquad, targetCategory) {
            this.deletePlayerFromCategory(sourceSquad, sourceCategory, player).then(() => {
                this.addPlayerToCategory(targetSquad, targetCategory, player)
            })
        },
        updateSquad(squad) {
            this.$apollo.mutate({
                                    mutation: gql`
                    mutation updateSquad($input: UpdateSquadInput!){
                        updateSquad(input: $input){
                            id
                            playerLimit
                            league
                            order
                        }
                    }
                `,
                                    variables: {
                                        input: {
                                            id: squad.id,
                                            playerLimit: squad.playerLimit,
                                            league: squad.league,
                                            order: squad.order
                                        }
                                    }
                                })
        },
        exportToCSV() {
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
                let file_path = data.export;
                let a = document.createElement('A');
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
        confirmChangeOfRankingList(newVersion) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Du er ved at skifte rangliste. Alle spillere på holdene vil bliver opdateret til den nye rangliste.',
                    confirmText: 'Skift og opdater spillere',
                    onConfirm: () => {
                        this.updateToRankingList()
                    },
                    onCancel: () => {
                        this.version = this.oldVersion
                    }
                })
        },
        focusNext(player) {
            const element = document.querySelector('[data-player-id-input="' + player.id + '"]')
            const inputs = document.querySelectorAll('input')
            const index = Array.from(inputs).indexOf(element) + 1
            if (inputs[index] !== undefined) {
                inputs[index].focus()
            }
        },
        addPlayer(squad, category, player) {
            this.addPlayerToCategory(squad, category, player)
                .then(({data}) => {
                    setTimeout(() => {
                        this.focusNext(data.createSquadMember)
                    }, 100);
                })
        },
        addPlayerToCategory(squad, category, player) {
            this.saving = true
            return this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation createSquadMember($input: CreateSquadMemberInput!){
                            createSquadMember(input: $input){
                                id
                                refId
                                name
                                gender
                                points {
                                    id
                                    category
                                    points
                                    position
                                    vintage
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            category: {
                                connect: category.id
                            },
                            gender: player.gender,
                            name: player.name,
                            refId: player.refId,
                            points: {
                                create: player.points.map((point) => {
                                    return {
                                        category: point.category,
                                        points: point.points,
                                        position: point.position,
                                        vintage: point.vintage
                                    }
                                })
                            }
                        }
                    },
                    refetchQueries: [
                        {query: TeamQuery, variables: {id: this.teamFightId}}
                    ],
                    awaitRefetchQueries: true
                })
                       .then((data) => {
                           this.$root.$emit('player-added-to-category', data.data.createSquadMember)
                           return data
                       })
                       .catch(() => {
                           this.$buefy.snackbar.open(
                               {
                                   duration: 4000,
                                   type: 'is-danger',
                                   message: `Kunne ikke tilføje spiller til holdet :(`
                               })
                       })
                       .finally(() => {
                           this.saving = false
                       })
        },
        deletePlayerFromCategory(squad, category, player) {
            this.saving = true
            return this.$apollo
                       .mutate({
                                   mutation: gql`
                                    mutation deleteSquadMember($id: ID!){
                                        deleteSquadMember(id: $id){
                                            id
                                        }
                                    }
                                `,
                                   variables: {
                                       id: player.id
                                   },
                                   refetchQueries: [
                                       {query: TeamQuery, variables: {id: this.teamFightId}}
                                   ],
                                   awaitRefetchQueries: true
                               })
                       .then(({data}) => {
                           this.$root.$emit('player-deleted-from-category', data.deleteSquadMember)
                       })
                       .catch((error) => {
                           this.$buefy.snackbar.open(
                               {
                                   duration: 4000,
                                   type: 'is-danger',
                                   queue: false,
                                   message: `Kunne ikke fjerne spilleren fra holdet :(`
                               })
                       })
                       .finally(() => {
                           this.saving = false
                       })
        },
        updateToRankingList() {
            this.updating = true;
            let version = this.version;
            return this.$apollo
                       .mutate(
                           {
                               mutation: gql`
                                    mutation ($id: ID!, $version: String!){
                                      updatePoints(id: $id, version: $version)
                                    }
                                `,
                               variables: {
                                   id: this.teamFightId,
                                   version: version
                               },
                               refetchQueries: [
                                   {query: TeamQuery, variables: {id: this.teamFightId}}
                               ]
                           })
                       .then(({data}) => {
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
                        input: wrapSquadsInTeamWithoutLeague(this.team.squads)
                    }
                })
                .then(({data}) => {
                    this.playingToHighSquadList = data.validateSquads;
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateSquad)`
                        })
                })
        },
        validateCrossSquads() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateCrossSquads($input: [ValidateTeam!]!){
                          validateCrossSquads(input: $input){
                            name
                            id
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
                        input: wrapInTeamAndSquads(this.team.squads)
                    }
                })
                .then(({data}) => {
                    this.playingToHighList = data.validateCrossSquads;
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validate)`
                        })
                })
        },
        validateWithSnackbar() {
            this.validate()
            this.$buefy.snackbar.open(
                {
                    duration: 10000,
                    type: 'is-success',
                    message: `Hold valideret. Tjek om nogle spiller er markeret. Husk valideringen køre automatisk når der sker ændringer på holdet.`
                })
        },
        validate() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation validateBasicSquads($input: [ValidateTeam!]!){
                          validateBasicSquads(input: $input){
                            index
                            spotsFulfilled
                          }
                        }
                    `,
                    variables: {
                        input: wrapInTeamAndSquads(this.team.squads)
                    }
                })
                .then(({data}) => {
                    this.validateBasicSquads = data.validateBasicSquads;
                    if (!this.resolveIncompleteTeam || this.ignoreIncompleteTeam) {
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
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validateSquad)`
                        })
                })
        },
        deleteTeam(targetSquad) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil slette hold ' + (this.team.squads.indexOf(targetSquad) + 1) + '?',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation deleteSquad($id: ID!){
                                        deleteSquad(id: $id){
                                            id
                                        }
                                    }
                                `,
                                variables: {
                                    id: targetSquad.id
                                },
                                refetchQueries: [
                                    {query: TeamQuery, variables: {id: this.teamFightId}}
                                ]
                            })
                    }
                })
        },
        selectClub(id) {
            this.clubId = id
        },
        addedPlayerNotification(squadIndex, category) {
            this.$buefy.snackbar.open(
                {
                    duration: 3000,
                    type: 'is-success',
                    queue: false,
                    message: 'Tilføjet til Hold ' + (squadIndex + 1) + ' i ' + category
                })
        },
        addPlayerToNextCategory(player) {
            let foundPlace = false;
            let addPlayerPromise;
            outside:
                for (const [index, squad] of this.team.squads.entries()) {
                    for (const category of squad.categories) {
                        if (isWomenDouble(category) && category.players.length < 2 && player.gender === 'K') {
                            this.addedPlayerNotification(index, category.name)
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                            foundPlace = true;
                            break outside;
                        } else if (isMensDouble(category) && category.players.length < 2 && player.gender === 'M') {
                            this.addedPlayerNotification(index, category.name)
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                            foundPlace = true;
                            break outside;
                        } else if (isMixDouble(category) && category.players.length < 2) {
                            if (category.players.length === 0) {
                                this.addedPlayerNotification(index, category.name)
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                                foundPlace = true;
                                break outside;
                            } else if (containsWomen(category) && player.gender === 'M') {
                                this.addedPlayerNotification(index, category.name)
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                                foundPlace = true;
                                break outside;
                            } else if (containsMen(category) && player.gender === 'K') {
                                this.addedPlayerNotification(index, category.name)
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                                foundPlace = true;
                                break outside;
                            }
                        } else if (isMensSingle(category) && category.players.length < 1 && player.gender === 'M') {
                            this.addedPlayerNotification(index, category.name)
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                            foundPlace = true;
                            break outside;
                        } else if (isWomensSingle(category) && category.players.length < 1 && player.gender === 'K') {
                            this.addedPlayerNotification(index, category.name)
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player)
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
            } else {
                return addPlayerPromise
            }
        },
        moveSquadOrderUp(squad) {
            this.saving = true
            this.$apollo.mutate({
                                    mutation: gql`
                                        mutation moveSquadOrderUp($input: ID!){
                                            moveSquadOrderUp(id: $input){
                                                id
                                                order
                                            }
                                        }
                                    `,
                                    variables: {
                                        input: squad.id
                                    },
                                    refetchQueries: [
                                        {query: TeamQuery, variables: {id: this.teamFightId}}
                                    ]
                                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Kunne ikke ændre sortering`
                        })
                })
                .finally(() => {
                    this.saving = false
                })
        },
        moveSquadOrderDown(squad) {
            this.saving = true
            this.$apollo.mutate({
                                    mutation: gql`
                                        mutation moveSquadOrderDown($input: ID!){
                                            moveSquadOrderDown(id: $input){
                                                id
                                                order
                                            }
                                        }
                                    `,
                                    variables: {
                                        input: squad.id
                                    },
                                    refetchQueries: [
                                        {query: TeamQuery, variables: {id: this.teamFightId}}
                                    ]
                                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Kunne ikke ændre sortering`
                        })
                })
                .finally(() => {
                    this.saving = false
                })
        },
        saveTeams() {
            if (this.saving === true) {
                return
            }
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
                    variables: {
                        input: {
                            id: this.teamFightId,
                            name: this.team.name,
                            version: this.version,
                            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate()
                        }
                    },
                    refetchQueries: [
                        {query: TeamQuery, variables: {id: this.teamFightId}}
                    ]
                })
                .then(({data}) => {
                    this.savingIcon = 'check';
                    setTimeout(() => {
                        this.savingIcon = 'save';
                    }, 2000);
                })
                .catch((error) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-danger',
                            message: `Kunne ikke gemme dit hold :(`
                        })
                })
                .finally(() => {
                    this.saving = false;
                })
        },
        copyTeamFight() {
            this.$buefy.dialog.confirm(
                {
                    message: 'Sikker på du vil kopier helt holdet? <br><br> Holdet kommer til at hedde "Kopi af ...." og du kan altid skifte ranglisten på det kopiret hold',
                    onConfirm: () => {
                        this.$apollo.mutate(
                            {
                                mutation: gql`
                                    mutation ($id: ID!){
                                        copyTeam(id: $id){
                                            id
                                            name
                                        }
                                    }
                                `,
                                variables: {
                                    id: this.teamFightId
                                }
                            }).then(({data}) => {
                            this.$buefy.snackbar.open(
                                {
                                    duration: 5000,
                                    type: 'is-success',
                                    message: "Dit nye hold hedder \"" + data?.copyTeam?.name + "\""
                                })
                            this.$router.push({name: 'team-fight-dashboard'})
                        })
                    }
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
    }
}
</script>

