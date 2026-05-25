<template>
    <div dusk="team-fight-edit-page">
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="true">
            {{ name || `Holdrunde nr. ${round}` }}
            <template v-slot:subtitle>
                <div class="is-flex is-align-items-center has-text-grey">
                    <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                    <span>Dato: {{ gameDate.toLocaleDateString('da-DK') }}</span>
                    <template v-if="version">
                        <b-icon icon="format-list-numbered" size="is-small" class="ml-3 mr-1"></b-icon>
                        <span>Rangliste: {{ timeToMonth(version) }}</span>
                    </template>
                </div>
            </template>
            <template v-slot:right>
                <b-button icon-left="pencil" @click="openSettingsModal">Rediger info</b-button>
                <b-button class="ml-2 is-pulled-right" icon-right="refresh" @click="refreshTeam" alt="Genindlæs holdrunden"></b-button>
            </template>
        </hero-bar>
        <section class="section is-main-section">
            <b-loading v-model="$apollo.loading || this.updating" :can-cancel="true" :is-full-page="true"></b-loading>
            <b-dropdown aria-role="list">
                <button slot="trigger" slot-scope="{ active }" class="button is-link">
                    <span>Del</span>
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
                <b-dropdown-item aria-role="listitem" @click="updateToRankingList">
                    <b-tooltip type="is-info" label="Opdater spillernes point på holdene med den valgte rangliste.">
                        <b-icon icon="update"></b-icon>
                        Opdater spillerpoint
                    </b-tooltip>
                </b-dropdown-item>
            </b-dropdown>
            <b-button class="ml-2" icon-left="email-fast" @click="notify">Send hold til spillere</b-button>
            <hr/>
            <div class="columns">
                <div class="column is-6">
                    <h1 class="title">Søg på spiller</h1>
                    <h1 class="subtitle">{{
                            hasMultipleClubs
                            ? 'Klubber:'
                            : 'Klub:'
                        }} {{ clubsNames }}
                        <router-link class="is-size-6" :to="{name: 'my-clubhouse', params: {clubhouseId: this.clubhouseId}, hash: '#add-clubs'}">(tilføj ekstra klub)</router-link>
                    </h1>
                    <PlayersListSearch :clubhouse-id="clubhouseId" :loading="saving" :add-player="addPlayerToNextCategory" :team-round-id="this.teamRoundId"
                                       :version="new Date(version)" :game-date="gameDate"/>
                </div>
                <div class="column is-6 container">
                    <h1 class="title">Holdene i holdrunden</h1>
                    <h1 class="subtitle">Træk spillerne rundt ved drag-and-drop</h1>
                    <ValidationStatus :incomplete-team="resolveIncompleteTeam" :invalid-category="resolveInvalidCategory"
                                      :invalid-level="resolveInvalidLevel"
                                      :basic-squads="validateBasicSquads"
                                      :invalid-category-list="playingToHighSquadList"
                                      :invalid-level-list="playingToHighList"
                                      :ignore-incomplete-team="ignoreIncompleteTeam"
                                      @update:ignoreIncompleteTeam="onIgnoreIncompleteTeamChange"/>
                    <TeamTable :confirm-delete="deleteTeam"
                               :delete-player="deletePlayerFromCategory"
                               :add-player="addPlayer"
                               :update-squad="updateSquad"
                               :move-squad-order-down="moveSquadOrderDown"
                               :move-squad-order-up="moveSquadOrderUp"
                               :player-move="playerMove"
                               :playing-to-high="playingToHighList"
                               :playing-to-high-in-squad="playingToHighSquadList"
                               :squads="teamRound.squads"
                               :teams-base-validations="validateBasicSquads"
                               :version="new Date(version)"
                               :loading="saving"
                    />
                    <hr>
                    <AddTeamsButtons :team-round-id="teamRoundId" :team-round-date="gameDate" :existing-squad-count="teamRound.squads.length"/>
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
import TeamRoundQuery from "../../../queries/teamRound.graphql"
import {hasInvalidCategory, hasInvalidLevel, wrapInTeamAndSquads, wrapSquadsInTeamWithoutLeague, timeToMonth} from "./helper";
import AddTeamsButtons from "./AddTeamsButtons.vue";
import ShareLinkModal from "./ShareLinkModal.vue";
import PlayersListSearch from "./PlayersListSearch.vue";
import ValidationStatus from "./ValidationStatus.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import TeamTable from "./TeamTable.vue";
import TeamRoundSettingsModal from "./TeamRoundSettingsModal.vue";
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
        TeamRoundSettingsModal,
        ValidateTeams,
        Draggable
    },
    props: {
        teamRoundId: String
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
            showLinkSharing: false,
            teamRound: {
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
        teamRound: {
            query: TeamRoundQuery,
            variables: function () {
                return {
                    id: this.teamRoundId
                }
            },
            result({data}) {
                this.gameDate = new Date(data.teamRound.gameDate);
                this.version = data.teamRound.version;
                this.name = data.teamRound.name;
                this.round = data.teamRound.round;
                this.validate()
            }
        }
    },
    methods: {
        timeToMonth,
        notify(){
            this.$router.push({name: 'team-fight-notify', params: {teamUUID: this.teamRoundId}})
        },
        refreshTeam(){
            this.$apollo.queries.teamRound.refetch();
        },
        openLinkSharingModal() {
            this.$buefy.modal.open({
                                       parent: this,
                                       component: ShareLinkModal,
                                       props: {
                                           teamRoundId: this.teamRoundId
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
        openSettingsModal() {
            this.$buefy.modal.open({
                                       parent: this,
                                       component: TeamRoundSettingsModal,
                                       props: {
                                           teamRound: this.teamRound
                                       },
                                       scroll: "keep",
                                        events: {
                                            close: () => {},
                                            save: () => {
                                                this.refreshTeam();
                                            }
                                        },
                                       width: 640
                                   })
        },
        openLinkSharingCancellationModel() {
            this.$router.push({name: 'cancellation-redirect'})
        },
        onIgnoreIncompleteTeamChange(value) {
            this.ignoreIncompleteTeam = value
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
                            order
                        }
                    }
                `,
                                    variables: {
                                        input: {
                                            id: squad.id,
                                            playerLimit: squad.playerLimit,
                                            order: squad.order
                                        }
                                    }
                                })
        },
        exportToCSV() {
            this.$apollo.query({
                                   query: gql`
                                        query exportToCSV($teamRoundId: ID!){
                                            export(teamRoundId:$teamRoundId)
                                        }
                                    `,
                                   variables: {
                                       teamRoundId: this.teamRoundId
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
                        {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
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
                                       {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
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
                                    mutation updatePointsTeamRound($id: ID!, $version: String!){
                                      updatePointsTeamRound(id: $id, version: $version){
                                        id
                                      }
                                    }
                                `,
                               variables: {
                                   id: this.teamRoundId,
                                   version: version
                               },
                               refetchQueries: [
                                   {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
                               ]
                           })
                       .then(({data}) => {
                           this.$buefy.snackbar.open(
                               {
                                   duration: 4000,
                                   type: 'is-success',
                                   message: `Points er nu ` + this.timeToMonth(version) + ' ranglisten'
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
                        input: wrapSquadsInTeamWithoutLeague(this.teamRound.squads)
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
                        input: wrapInTeamAndSquads(this.teamRound.squads)
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
                        input: wrapInTeamAndSquads(this.teamRound.squads)
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
                    message: 'Sikker på du vil slette hold ' + (this.teamRound.squads.indexOf(targetSquad) + 1) + '?',
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
                                    {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
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
                for (const [index, squad] of this.teamRound.squads.entries()) {
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
                                        {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
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
                                        {query: TeamRoundQuery, variables: {id: this.teamRoundId}}
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
    }
}
</script>
