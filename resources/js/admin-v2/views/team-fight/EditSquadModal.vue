<script>
import debounce from "lodash/debounce";
import gql from "graphql-tag";
import {formatDateTime, parseDateTime} from "../../helpers";
import BadmintonPlayerTeamFightSelector from "./BadmintonPlayerTeamFightSelector.vue";

export default {
    name: "EditSquadModal",
    components: {BadmintonPlayerTeamFightSelector},
    props: {
//        updateSquad: Function,
        squad: Object
    },
    watch: {
        squad: {
            handler(newValue, oldValue) {
                this.league = newValue.league
                this.name = newValue.name
                this.playingDatetime = new Date(newValue.playingDatetime)
                this.playingPlace = newValue.playingPlace
                this.playingAddress = newValue.playingAddress
                this.playingZipCode = newValue.playingZipCode
                this.playingCity = newValue.playingCity
            },
            immediate: true
        }
    },
    data() {
        return {
            loading: false,
            name: null,
            playingDatetime: null,
            playingPlace: null,
            playingAddress: null,
            playingZipCode: null,
            playingCity: null,
            externalTeamFightID: null,
            address: [],
            league: null,
            isFetching: false,
            showTeamFightSelector: false,
            leagueOptions: [
                {label: 'Normal', value: "OTHER"},
                {label: "1. Division", value: "FIRSTDIVISION"},
                {label: "Liga", value: "LIGA"}
            ]
        }
    },
    methods: {
        updateSquad() {
            this.$apollo.mutate({
                                    mutation: gql`
                    mutation updateSquad($input: UpdateSquadInput!){
                        updateSquad(input: $input){
                            id
                            league
                            name
                            playingDatetime
                            playingPlace,
                            playingAddress
                            playingZipCode
                            playingCity
                            externalTeamFightID
                        }
                    }
                `,
                                    variables: {
                                        input: {
                                            id: this.squad.id,
                                            league: this.league,
                                            name: this.name,
                                            externalTeamFightID: this.externalTeamFightID,
                                            playingDatetime: formatDateTime(this.playingDatetime),
                                            playingPlace: this.playingPlace,
                                            playingAddress: this.playingAddress,
                                            playingZipCode: this.playingZipCode,
                                            playingCity: this.playingCity
                                        }
                                    }
                                })
        },
        fillInformation(teamFight, playerTeam){
            this.externalTeamFightID = teamFight.matchId
            this.playingDatetime = parseDateTime(teamFight.gameTime)
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
                    console.log(data)
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
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Rediger {{name}}</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <b-message type="is-info">Brug kun denne funktion hvis spilleren ikke findes på nembadminton.dk men på badmintonplayer.dk.</b-message>
                                <p></p>
                <b-field label="Hold navn">
                    <b-input
                        type="text"
                        v-model="name"
                        placeholder="Danmarksserien Pulje 1">
                    </b-input>
                </b-field>
                <b-field label="Hold type">
                    <b-select expanded v-model="league" required>
                        <option v-for="leagueOption in leagueOptions" :value="leagueOption.value" :key="leagueOption.value">{{ leagueOption.label }}</option>
                    </b-select>
                </b-field>
                <hr/>
                <b-field grouped>
<!--                    <b-field>-->
<!--                        <b-input-->
<!--                            type="text"-->
<!--                            v-model="externalTeamFightID"-->
<!--                            placeholder="446437"-->
<!--                        />-->
<!--                        <p class="control">-->
<!--                            <b-button type="is-primary" label="Import" />-->
<!--                        </p>-->
<!--                    </b-field>-->
<!--                    eller -->
                    <b-button v-if="!showTeamFightSelector" @click="showTeamFightSelector = !showTeamFightSelector">Hent informationer fra badmintonplayer.dk</b-button>
                    <BadmintonPlayerTeamFightSelector :import-information="fillInformation" v-if="showTeamFightSelector" />
                </b-field>
                <b-field label="Spille start">
                    <b-datetimepicker
                        placeholder="Vælge spille dato og tidspunkt"
                        icon="calendar-today"
                        locale="da-DK"
                        v-model="playingDatetime"
                        editable>
                    </b-datetimepicker>
                </b-field>
                <b-field label="Spille sted">
                    <b-input
                        type="text"
                        v-model="playingPlace"
                        placeholder="Valbyhallen Hal 2"
                    />
                </b-field>
                <b-field label="Spille addrese">
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
                <!--                <b-field label="Spille addrese">-->
                <!--                    <b-autocomplete-->
                <!--                        :data="address"-->
                <!--                        @typing="getAsyncData"-->
                <!--                        v-model="playingAddress"-->
                <!--                    >-->
                <!--                        <template v-slot:default="props">-->
                <!--                            {{ props.option }}-->
                <!--                        </template>-->
                <!--                    </b-autocomplete>-->
                <!--                </b-field>-->
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

</style>
