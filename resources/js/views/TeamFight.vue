<template>
    <div>
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
                <span>Indstillinger</span>
                <b-icon :icon="active ? 'angle-up' : 'angle-down'"></b-icon>
            </button>
            <b-dropdown-item aria-role="listitem" @click="validateWithSnackbar">
                <b-icon icon="brain"></b-icon>
                Validere hold
            </b-dropdown-item>
            <b-dropdown-item aria-role="listitem" @click="updateToRankingList">
                <b-tooltip label="Opdater spillernes point med den valgte rangliste.">
                    <b-icon icon="rotate"></b-icon>
                    Opdater spiller point
                </b-tooltip>
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
            <b-dropdown-item aria-role="listitem" @click="showLinkSharing = true">
                <b-icon icon="share-alt"></b-icon>
                <share-link-modal v-model="showLinkSharing" :team-id="teamFightId"></share-link-modal>
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
                    <router-link class="is-size-6" :to="{name: 'my-clubs'}">(tilføj ny klub)</router-link>
                </h1>
                <PlayersListSearch :loading="saving" :add-player="addPlayerToNextCategory" :team-id="this.teamFightId" :club-id="team.club.id"
                                   :version="new Date(version)"/>
            </div>
            <div class="column is-6 container">
                <h1 class="title">Holdet</h1>
                <h1 class="subtitle">Træk spillerne rundt ved at drag-and-drop</h1>
                <TeamTable :confirm-delete="deleteTeam"
                           :delete-player="deletePlayerFromCategory"
                           :add-player="addPlayer"
                           :update-squad="updateSquad"
                           :change-order="changeOrder"
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
                <AddTeamsButtons :team-id="teamFightId" :next-order="team?.squads?.length" @team-added="$apollo.queries.team.refresh()"/>
            </div>
        </div>
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
import ShareLinkModal from "../components/team-fight/ShareLinkModal";
import TeamQuery from "../queries/team.graphql"
import {hasInvalidCategory, hasInvalidLevel} from "./team-fight/helper";
import ME from "../queries/me.gql";
import AddTeamsButtons from "./team-fight/AddTeamsButtons";
import MemberSearchTeamFight from "../queries/memberSearchTeamFight.gql";

export default {
    name: "TeamFight",
    components: {
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
            savingIcon: 'save',
            showLinkSharing: false,
            team: {
                squads: [],
                club: {}
            }
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
        wrapInTeamAndSquads(squads) {
            const squadsClone = JSON.parse(JSON.stringify(squads));
            return omitDeep(squadsClone, ['__typename', 'cancellations', 'isInSquad', 'order']).map((squad) => ({
                name: 'Team X',
                squad: squad
            }))
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
                    }, 20);
                })
        },
        addPlayerToCategory(squad, category, player) {
            this.saving = true
            return this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation createSquadMember($input: CreatePlayerInput!){
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
                    update: (store, {data: {createSquadMember}}) => {
                        let variables = {id: this.teamFightId};
                        let data = store.readQuery({query: TeamQuery, variables: variables})
                        let squadIndex = this.team.squads.findIndex(squadOriginal => squadOriginal.id === squad.id);
                        let squadCache = data.team.squads[squadIndex]
                        let categoryIndex = squadCache.categories.findIndex(categoryOriginal => categoryOriginal.id === category.id);
                        data.team.squads[squadIndex].categories[categoryIndex].players.push(createSquadMember)
                        store.writeQuery({query: TeamQuery, data, variables})
                    }
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
                                   update: (store, {data: {deleteSquadMember}}) => {
                                       let variables = {id: this.teamFightId};
                                       let data = store.readQuery({query: TeamQuery, variables: variables})
                                       let squadIndex = this.team.squads.findIndex(squadOriginal => squadOriginal.id === squad.id);
                                       let squadCache = data.team.squads[squadIndex]
                                       let categoryIndex = squadCache.categories.findIndex(categoryOriginal => categoryOriginal.id === category.id);
                                       let categoryCache = squadCache.categories[categoryIndex]
                                       let playerIndex = categoryCache.players.findIndex(playerOriginal => playerOriginal.id === player.id)
                                       data.team.squads[squadIndex].categories[categoryIndex].players.splice(playerIndex, 1)
                                       store.writeQuery({query: TeamQuery, data, variables})
                                   },
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
                               }
                           })
                       .then(({data}) => {
                           this.$apollo.queries.team.setOptions({
                                                                    fetchPolicy: 'network-only'
                                                                })
                           this.$apollo.queries.team.refresh()
                           this.$apollo.queries.team.setOptions({
                                                                    fetchPolicy: 'cache-first'
                                                                })
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
                            type: 'is-danger',
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
                            type: 'is-danger',
                            queue: false,
                            message: `Noget gik galt under valideringen af holdet (validate)`
                        })
                })
        },
        validateWithSnackbar(){
            this.validate()
            this.$buefy.snackbar.open(
                {
                    duration: 10000,
                    type: 'is-success',
                    message: `Hold valideret. Tjek om nogle spiller er markeret. Husk valideringen køre automatisk når der sker ændringer på holdet.`
                })
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
                                update: (store, {data: {deleteSquad}}) => {
                                    let variables = {id: this.teamFightId};
                                    let data = store.readQuery({query: TeamQuery, variables: variables})
                                    data.team.squads.splice(this.team.squads.indexOf(targetSquad), 1)
                                    store.writeQuery({query: TeamQuery, data, variables})
                                },
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
        changeOrder(fromSquad, toSquad) {
            this.saving = true
            this.$apollo.mutate({
                                    mutation: gql`
                                        mutation updateSquad($input: UpdateSquadInput!){
                                            updateSquad(input: $input){
                                                id
                                                order
                                            }
                                        }
                                    `,
                                    variables: {
                                        input: {
                                            id: fromSquad.id,
                                            order: toSquad.order
                                        }
                                    }
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
            this.$apollo
                .mutate({
                            mutation: gql`
                                mutation updateSquad($input: UpdateSquadInput!){
                                    updateSquad(input: $input){
                                        id
                                        order
                                    }
                                }
                            `,
                            variables: {
                                input: {
                                    id: toSquad.id,
                                    order: fromSquad.order
                                }
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

