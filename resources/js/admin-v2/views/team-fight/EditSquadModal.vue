<script>
import debounce from "lodash/debounce";
import gql from "graphql-tag";
import {formatDateTime, getCurrentSeason, parseDateTime} from "../../helpers";
import BadmintonPlayerTeamFightSelector from "./BadmintonPlayerTeamFightSelector.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import {timeToMonth} from "./helper";

export default {
    name: "EditSquadModal",
    components: {RankingVersionSelect, BadmintonPlayerTeamFightSelector},
    props: {
        squad: Object
    },
    watch: {
        squad: {
            handler(newValue, oldValue) {
                this.name = newValue.name
                this.tier = newValue.tier
                this.team = newValue.team
                this.playingDatetime = newValue.playingDatetime
                                       ? new Date(newValue.playingDatetime)
                                       : null
                this.playingPlace = newValue.playingPlace
                this.playingAddress = newValue.playingAddress
                this.playingZipCode = newValue.playingZipCode
                this.playingCity = newValue.playingCity
                this.externalTeamFightID = newValue.externalTeamFightID
                this.version = newValue.version
                this.oldVersion = newValue.version
            },
            immediate: true
        }
    },
    computed: {
        getCurrentSeason,
        hasTeam() {
            return this.team !== null && this.team !== undefined;
        },
        teamChipLabel() {
            if (!this.hasTeam) {
                return '';
            }
            const tierLabel = this.team?.tier?.tierName || this.team?.customTierName || '';
            const parts = [this.team.name];
            if (tierLabel) parts.push(tierLabel);
            if (this.team.groupName) parts.push(this.team.groupName);
            return parts.join(' · ');
        }
    },
    data() {
        return {
            loading: false,
            name: null,
            tier: null,
            team: null,
            playingDatetime: null,
            playingPlace: null,
            playingAddress: null,
            playingZipCode: null,
            playingCity: null,
            externalTeamFightID: null,
            address: [],
            version: null,
            isFetching: false,
            showTeamFightSelector: false,
            oldVersion: null,
            changeOfRankingWarning: false
        }
    },
    methods: {
        timeToMonth,
        toggleRankingWarning(){
            this.changeOfRankingWarning = true;
        },
        disconnectTeam() {
            this.team = null;
        },
        updateToRankingList(newVersion) {
            return this.$apollo
                       .mutate(
                           {
                               mutation: gql`
                                    mutation updatePointsSquad($id: ID!, $version: String){
                                      updatePointsSquad(id: $id, version: $version){
                                        id
                                        playerLimit
                                        order
                                        name
                                          tier
                                        playingCity
                                        playingZipCode
                                        playingAddress
                                        playingPlace
                                        playingDatetime
                                        externalTeamFightID
                                        version
                                          team {
                                              id
                                              name
                                              groupName
                                              tier{
                                                  id
                                                  tierName
                                              }
                                          }
                                        categories{
                                            id
                                            category
                                            name
                                            players{
                                                id
                                                gender
                                                name
                                                refId
                                                points{
                                                    id
                                                    category
                                                    points
                                                    position
                                                    vintage
                                                    corrected_manually
                                                    version
                                                }
                                            }
                                        }
                                      }
                                    }
                                `,
                               variables: {
                                   id: this.squad.id,
                                   version: newVersion
                               }
                           })
                       .then(({data}) => {
                           this.$buefy.snackbar.open(
                               {
                                   duration: 4000,
                                   type: 'is-success',
                                   queue: false,
                                   message: `Points er nu ` + (newVersion !== null
                                                               ? timeToMonth(newVersion) + ' ranglisten'
                                                               : 'ændret')
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
        },
        updateSquad() {
            this.loading = true
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updateSquad($input: UpdateSquadInput!){
                            updateSquad(input: $input){
                                id
                                name
                                tier
                                team {
                                    id
                                    name
                                    groupName
                                    tier{
                                        id
                                        tierName
                                    }
                                }
                                playingDatetime
                                playingPlace,
                                playingAddress
                                playingZipCode
                                playingCity
                                externalTeamFightID
                                version
                            }
                        }
                    `,
                    variables: {
                        input: {
                            id: this.squad.id,
                            name: this.name,
                            tier: this.tier,
                            teamId: this.team?.id ?? null,
                            externalTeamFightID: this.externalTeamFightID,
                            playingDatetime: this.playingDatetime
                                             ? formatDateTime(this.playingDatetime)
                                             : null,
                            playingPlace: this.playingPlace,
                            playingAddress: this.playingAddress,
                            playingZipCode: this.playingZipCode,
                            playingCity: this.playingCity,
                            version: this.version
                        }
                    }
                })
                .then(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 5000,
                            type: 'is-success',
                            queue: false,
                            message: `Hold opdateret`,
                        })
                    if (this.oldVersion !== this.version) {
                        return this.updateToRankingList(this.version)
                    }
                })
                .then(() => {
                    this.$emit('close')
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                               duration: 5000,
                                               message: `Fejl :( Kunne ikke opdater holdet`,
                                               type: 'is-danger'
                                           })

                })
                .finally(() => {
                    this.loading = false
                })
        },
        fillInformation(teamFight, playerTeam) {
            this.externalTeamFightID = teamFight.matchId
            this.$apollo.query(
                {
                    query: gql`
                        query TeamMatch($input: BadmintonPlayerTeamMatchInput){
                          badmintonPlayerTeamMatch(input: $input){
                                playingPlace
                                playingAddress
                                playingZipCode
                                playingCity

                            }
                        }
                    `,
                    variables: {
                        input: {
                            leagueMatchId: teamFight.matchId,
                            season: this.getCurrentSeason
                        }
                    }
                })
                .then(({data}) => {
                    this.playingPlace = data.badmintonPlayerTeamMatch.playingPlace
                    this.playingAddress = data.badmintonPlayerTeamMatch.playingAddress
                    this.playingCity = data.badmintonPlayerTeamMatch.playingCity
                    this.playingZipCode = data.badmintonPlayerTeamMatch.playingZipCode
                    this.$buefy.snackbar.open({
                                               duration: 5000,
                                               message: `Informationer udfyldt. Tjek dem igennem om de er korrekt`,
                                               type: 'is-success'
                                           })
                    this.showTeamFightSelector = false
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                               duration: 5000,
                                               message: `Fejl :( Kunne ikke hente informationer automatisk fra badmintonplayer`,
                                               type: 'is-danger'
                                           })
                })

            this.name = playerTeam.league
        },
        // You have to install and import debounce to use it,
        // it's not mandatory though.
        getAsyncData: debounce(function (name) {
            if (!name.length) {
                this.address = []
                return
            }
            this.isFetching = true
            let urlSearchParams = new URLSearchParams({
                                                          type: 'vejnavn',
                                                          q: name,
                                                          fuzzy: true,
                                                          //caretpos: caretpos,
                                                          multilinje: true
                                                      });
            fetch('https://api.dataforsyningen.dk/autocomplete?' + urlSearchParams)
                .then((data) => {
                    return data.json();
                })
                .then((data) => {
                    this.address = []
                    data.forEach((item) => this.address.push(item))
                })
                .catch((error) => {
                    this.address = []
                    throw error
                })
                .finally(() => {
                    this.isFetching = false
                })
        }, 500)
    }
}
</script>

<template>
    <form @submit.prevent="updateSquad">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Rediger {{ name }}</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <b-message type="is-info">Alle informationerne er tilgængelig når holdrunden deles via link</b-message>
                <b-field v-if="!showTeamFightSelector" message="Udfylder holdnavn, kampnummer, spillestart, spillested, adresse, postnummer og by">
                    <b-button type="is-info" @click="showTeamFightSelector = !showTeamFightSelector">Udfyld felter med data fra badmintonplayer.dk</b-button>
                </b-field>
                <b-button v-if="showTeamFightSelector" type="is-info" @click="showTeamFightSelector = !showTeamFightSelector">Luk</b-button>
                <BadmintonPlayerTeamFightSelector :import-information="fillInformation" v-if="showTeamFightSelector"/>
                <hr>
                <b-field
                    v-if="hasTeam"
                    label="Tilknyttet hold"
                    message="Holdnavn og niveau styres af det tilknyttede hold. Frakobl for at redigere frit.">
                    <div class="edit-squad-modal__team-chip" dusk="edit-squad-team-chip">
                        <b-icon icon="shield-account" size="is-small" class="mr-2"/>
                        <span class="edit-squad-modal__team-chip-label">{{ teamChipLabel }}</span>
                        <b-button
                            class="ml-2"
                            type="is-text"
                            size="is-small"
                            icon-right="close"
                            :disabled="loading"
                            dusk="disconnect-squad-team"
                            @click="disconnectTeam">
                            Frakobl
                        </b-button>
                    </div>
                </b-field>
                <b-field label="Holdnavn">
                    <b-input
                        :disabled="hasTeam"
                        type="text"
                        v-model="name"
                        placeholder="fx Højbjerg 1">
                    </b-input>
                </b-field>
                <b-field label="Niveau">
                    <b-input
                        :disabled="hasTeam"
                        type="text"
                        v-model="tier"
                        placeholder="fx 1. division">
                    </b-input>
                </b-field>
                <hr/>
                <b-field label="Rangliste" message="Vælg en anden rangliste end holdrundens, hvis der indenfor samme spillerunde skal anvendes forskellige ranglister">
                    <RankingVersionSelect @change="toggleRankingWarning" placeholder="Ingen rangliste valgt (bruger ranglisten fra holdrunden)" v-model="version" expanded></RankingVersionSelect>
                    <p class="control">
                        <b-button type="is-link" @click="version = null; toggleRankingWarning()">Nulstil</b-button>
                    </p>
                </b-field>
                <b-message v-if="changeOfRankingWarning" type="is-info">
                    Pointene på holdet opdates til den valgte rangliste når der trykkes på gem
                </b-message>
                <b-field message="Giver mulighed for link til badmintonplayer. Kan ses hvis holdkampen deles via link" label="BadmintonPlayer kampnummer">
                    <b-input
                        type="number"
                        v-model.number="externalTeamFightID"
                        placeholder="446437"
                        expanded
                    />
                    <p class="control">
                        <b-button icon-right="open-in-new" title="Link til badmintonplayer. Kræver kampnummer" :disabled="!!!externalTeamFightID" class="is-pulled-right" tag="a" target="_blank"
                                  :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+getCurrentSeason+',,,,,'+externalTeamFightID+',,'"
                                  type="is-link">
                        </b-button>
                    </p>
                </b-field>
                <b-field label="Spillestart">
                    <b-datetimepicker
                        placeholder="Vælg dato og tidspunkt"
                        icon="calendar-today"
                        locale="da-DK"
                        v-model="playingDatetime"
                        expanded
                        editable>
                    </b-datetimepicker>
                    <p class="control">
                        <b-button type="is-link" @click="playingDatetime = null">Nulstil</b-button>
                    </p>
                </b-field>
                <b-field label="Spillested">
                    <b-input
                        type="text"
                        v-model="playingPlace"
                        placeholder="Valbyhallen Hal 2"
                    />
                </b-field>
                <b-field label="Adresse">
                    <b-input
                        type="text"
                        v-model="playingAddress"
                        placeholder="Julius Andersens Vej 3"
                    >
                    </b-input>
                </b-field>
                <b-field label="Postnummer">
                    <b-input
                        type="text"
                        v-model="playingZipCode"
                        placeholder="8000"
                    >
                    </b-input>
                </b-field>
                <b-field label="By">
                    <b-input
                        type="text"
                        v-model="playingCity"
                        placeholder="Århus C"
                    >
                    </b-input>
                </b-field>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    :loading="this.loading"
                    native-type="submit"
                    label="Gem"/>
                <b-button
                    :loading="this.loading"
                    @click="$emit('close')"
                    label="Luk"/>
            </footer>
        </div>
    </form>
</template>

<style scoped>
.edit-squad-modal__team-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.5rem 0.35rem 0.75rem;
    border: 1px solid #b5b5b5;
    border-radius: 999px;
    background: #fff;
    color: #363636;
    font-size: 0.95rem;
    max-width: 100%;
}

.edit-squad-modal__team-chip-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
