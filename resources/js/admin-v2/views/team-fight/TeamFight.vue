<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Dashboard
        </hero-bar>
        <section class="section is-main-section">
            <b-loading v-model="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
            <b-tooltip type="is-info" label="Auto-save er slået til. Auto-save sker KUN når der sker ændringer på holdet"
                       position="is-bottom">
                <b-button :loading="saving" :class="{'is-success': this.savingIcon === 'check'}" :icon-left="savingIcon"
                          @click="saveTeams">Gem
                </b-button>
            </b-tooltip>
            <!--        <b-button icon-left="bell" @click="notify">Notificer</b-button>-->
            <b-dropdown aria-role="list" class="ml-2">
                <button slot="trigger" slot-scope="{ active }" class="button is-link">
                    <span>Eksporter</span>
                    <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                </button>
                <b-dropdown-item aria-role="listitem" @click="exportToCSV">
                    <b-icon icon="file-export"></b-icon>
                    CSV
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="openLinkSharingModal">
                    <b-icon icon="share"></b-icon>
                    Link
                </b-dropdown-item>
            </b-dropdown>
            <b-dropdown aria-role="list" class="ml-2">
                <button slot="trigger" slot-scope="{ active }" class="button is-link">
                    <span>Indstillinger</span>
                    <b-icon :icon="active ? 'arrow-up' : 'arrow-down'"></b-icon>
                </button>
                <b-dropdown-item aria-role="listitem" @click="validateWithSnackbar">
                    <b-icon icon="brain"></b-icon>
                    Valider holdrunden
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="deactivateIncompleteCheck">
                    <b-tooltip type="is-info" label="Kan bruges hvis du ikke kan stille et fuld hold">
                        <b-icon icon="cancel"></b-icon>
                        {{ignoreIncompleteTeam ? 'Aktiver' : 'Deaktiver'}} "Fuldendt hold" check
                    </b-tooltip>
                </b-dropdown-item>
                <b-dropdown-item aria-role="listitem" @click="updateToRankingList">
                    <b-tooltip type="is-info" label="Opdater spillernes point på holdene med den valgte rangliste.">
                        <b-icon icon="update"></b-icon>
                        Opdater spillerpoint
                    </b-tooltip>
                </b-dropdown-item>
            </b-dropdown>
            <b-button class="ml-2 is-pulled-right" icon-right="refresh" @click="refreshTeam">Genindlæs holdrunden</b-button>
            <div class="columns mt-2">
                <div class="column">
                    <b-field label="Navn">
                        <b-input v-model="name" placeholder="fx. Runde 1"></b-input>
                    </b-field>
                </div>
                <!--                <div class="column">-->
                <!--                    <b-field label="Runde">-->
                <!--                        <b-numberinput v-model="round" :min="0" :max="10"></b-numberinput>-->
                <!--                    </b-field>-->
                <!--                </div>-->
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
                        <router-link class="is-size-6" :to="{name: 'my-clubhouse', params: {clubhouseId: this.clubhouseId}}">(tilføj ekstra klub)</router-link>
                    </h1>
                    <PlayersListSearch :clubhouse-id="clubhouseId" :loading="saving" :add-player="addPlayerToNextCategory" :team-id="this.teamFightId" :club-id="team.club.id"
                                       :version="new Date(version)" :game-date="gameDate"/>
                </div>
                <div class="column is-6 container">
                    <h1 class="title">Holdene i holdrunden</h1>
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
    containsWomen, extractErrorMessages,
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
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import clubhouse from "../../../queries/clubhouse.gql";

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
        Draggable
    },
    props: {
        teamFightId: String
    },
    inject: {
        clubhouseId: {
            default: 0
        }
    },
    computed: {
        hasMultipleClubs() {
            return this.clubhouse?.clubs?.length > 1;
        },
        clubsNames() {
            return this.clubhouse?.clubs?.map((club) => {
                return club.name1
            }).join(', ')
        },
        resolveIncompleteTeam() {
            if (this.ignoreIncompleteTeam) {
                return null
            }
            if (this.validateBasicSquads.length === 0) {
                return null
            }
            return this.validateBasicSquads.find(data => data.missingPlayerInCategory === true || data.spotsFulfilled === false) !== undefined
        },
        resolveInvalidCategory() {
            if (this.errorValidatingCategory) {
                return null
            }
            if (!this.canValidateSquads) {
                return null
            }
            return hasInvalidCategory(this.playingToHighSquadList)
        },
        resolveInvalidLevel() {
            if (this.errorValidatingLevel) {
                return null
            }
            if (!this.canValidateCrossSquads) {
                return null
            }
            return hasInvalidLevel(this.playingToHighList)
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Holdrunde'],
            validateBasicSquads: [],
            playingToHighList: [],
            playingToHighSquadList: [],
            teamCount: 1,
            players: [],
            saving: false,
            updating: false,
            gameDate: new Date(),
            version: null,
            round: null,
            name: '',
            oldVersion: null,
            savingIcon: 'content-save',
            showLinkSharing: false,
            team: {
                squads: [],
                club: {}
            },
            ignoreIncompleteTeam: false,
            canValidateCrossSquads: false,
            canValidateSquads: false,
            errorValidatingCategory: false,
            errorValidatingLevel: false
        }
    },
    apollo: {
        clubhouse: {
            query: clubhouse,
            variables(){
                return {
                    id: this.clubhouseId
                }
            }
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
                this.name = data.team.name;
                this.round = data.team.round;
                this.validate()
            }
        }
    },
    methods: {
        refreshTeam(){
            this.$apollo.queries.team.refetch();
        },
        openLinkSharingModal() {
            this.$buefy.modal.open({
                                       parent: this,
                                       component: ShareLinkModal,
                                       props: {
                                           teamId: this.teamFightId
                                       },
                                       scroll: "keep",
                                       width: 640,
                                       events: {
                                           close: () => {
                                               this.$emit('input', false)
                                           }
                                       }
                                   })
        },
        openLinkSharingCancellationModel() {
            this.$router.push({name: 'cancellation-redirect'})
        },
        deactivateIncompleteCheck() {
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
                    message: 'Du er ved at skifte rangliste. Alle spillere på holdene vil bliver opdateret til den nye rangliste. Hold med en specifik rangliste vil ikke blive opdateret',
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
                        this.focusNext(data.addSquadMemberByRefId)
                    }, 100);
                })
        },
        addPlayerToCategory(squad, category, player) {
            this.saving = true
            return this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation addSquadMemberByRefId($input: AddSquadMemberByRefIdInput!){
                            addSquadMemberByRefId(input: $input){
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
                                    version
                                }
                            }
                        }
                    `,
                    variables: {
                        input: {
                            categoryId: parseInt(category.id),
                            refId: player.refId,
                            version: squad.version
                                     ? squad.version
                                     : this.version
                        }
                    },
                    refetchQueries: [
                        {query: TeamQuery, variables: {id: this.teamFightId}}
                    ],
                    awaitRefetchQueries: true
                })
                       .then((data) => {
                           this.$root.$emit('player-added-to-category', data.data.addSquadMemberByRefId)
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
                                    mutation updatePointsTeam($id: ID!, $version: String!){
                                      updatePointsTeam(id: $id, version: $version){
                                        id
                                      }
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
            this.errorValidatingCategory = false;
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
                .catch(({graphQLErrors}) => {
                    this.errorValidatingCategory = true;
                    const errorMessages = extractErrorMessages(graphQLErrors);
                    this.$buefy.snackbar.open(
                        {
                            duration: 5000,
                            type: 'is-danger',
                            queue: false,
                            message: 'Noget gik galt under intern valideringen af holdet. <br/><br /> '+errorMessages.join(', ')
                        })
                })
        },
        validateCrossSquads() {
            this.errorValidatingLevel = false
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
                                category
                                balance
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
                    this.errorValidatingLevel = true
                    this.$buefy.snackbar.open(
                        {
                            duration: 4000,
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (crossSquadsValidate)`
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
                        if (isWomenDouble(category) && category.players.length < 2 && player.gender === 'WOMEN') {
                            this.addedPlayerNotification(index, category.name)
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                            foundPlace = true;
                            break outside;
                        } else if (isMensDouble(category) && category.players.length < 2 && player.gender === 'MEN') {
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
                            } else if (containsWomen(category) && player.gender === 'MEN') {
                                this.addedPlayerNotification(index, category.name)
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                                foundPlace = true;
                                break outside;
                            } else if (containsMen(category) && player.gender === 'WOMEN') {
                                this.addedPlayerNotification(index, category.name)
                                addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                                foundPlace = true;
                                break outside;
                            }
                        } else if (isMensSingle(category) && category.players.length < 1 && player.gender === 'MEN') {
                            this.addedPlayerNotification(index, category.name)
                            addPlayerPromise = this.addPlayerToCategory(squad, category, player)
                            foundPlace = true;
                            break outside;
                        } else if (isWomensSingle(category) && category.players.length < 1 && player.gender === 'WOMEN') {
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
                            round
                          }
                        }
                    `,
                    variables: {
                        input: {
                            id: this.teamFightId,
                            name: this.name,
                            version: this.version,
                            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                            round: this.round
                        }
                    },
                    refetchQueries: [
                        {query: TeamQuery, variables: {id: this.teamFightId}}
                    ]
                })
                .then(({data}) => {
                    this.savingIcon = 'check';
                    setTimeout(() => {
                        this.savingIcon = 'content-save';
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
        }
    }
}
</script>

