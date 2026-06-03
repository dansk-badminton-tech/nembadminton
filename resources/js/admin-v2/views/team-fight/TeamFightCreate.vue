<template>
    <div dusk="team-fight-create-page">
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Opret holdrunde
        </hero-bar>
        <section class="section is-main-section">
            <form @submit.prevent="createTeamRound" v-if="!$apollo.queries.me.loading" class="column">
                <b-field label="Runde nr.">
                    <b-numberinput
                        dusk="team-fight-round-input"
                        v-model="round"
                        :min="1"
                        controls-position="compact"
                        required>
                    </b-numberinput>
                </b-field>
                <b-field>
                    <template v-slot:label>
                        Runde spilledato
                    </template>
                    <b-datepicker
                        dusk="team-fight-date-picker"
                        v-model="gameDate"
                        icon="calendar"
                        placeholder="Klik for at vælge dato..."
                        locale="da-DK"
                        :first-day-of-week="1"
                        trap-focus>
                    </b-datepicker>
                </b-field>
                <b-field label="Rangliste">
                    <RankingVersionSelect dusk="team-fight-ranking-select" required v-model="version" :playing-date="gameDate" expanded/>
                </b-field>
                <b-field label="Sæson">
                    <b-select
                        v-model="seasonId"
                        dusk="team-fight-season-select"
                        :loading="$apollo.queries.seasons.loading"
                        placeholder="Vælg sæson"
                        expanded
                        required>
                        <option
                            v-for="season in seasons"
                            :key="season.id"
                            :value="season.id">
                            {{ season.seasonName }}
                        </option>
                    </b-select>
                </b-field>
                <b-field label="Navn (Valgfrit)">
                    <b-input dusk="team-fight-name-input" v-model="name" expanded></b-input>
                </b-field>
                <b-button dusk="team-fight-submit-button" class="mt-2" native-type="submit" label="Opret" icon-left="plus" :loading="loading" />
            </form>
        </section>
    </div>
</template>

<script>
import gql from 'graphql-tag'
import ME from "../../../queries/me.gql";
import SeasonsQuery from "../../../queries/seasons.graphql";
import RankingListDatePicker from "../common/RankingListDatePicker.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import { resolveSeasonIdByDate } from "./add-squad-metadata";

export default {
    name: "TeamFightCreate",
    components: {HeroBar, TitleBar, RankingVersionSelect, RankingListDatePicker},
    data() {
        return {
            titleStack: ['Admin', 'Holdrunder'],
            name: null,
            gameDate: null,
            clubId: null,
            version: null,
            round: null,
            loading: false,
            seasons: [],
            seasonId: null,
            seasonManuallyChanged: false
        }
    },
    apollo: {
        me: {
            query: ME,
            result({data}) {
                this.clubId = data.me.clubhouse.clubs[0].id
            }
        },
        seasons: {
            query: SeasonsQuery
        }
    },
    watch: {
        gameDate(newDate) {
            if (this.seasonManuallyChanged) {
                return;
            }
            const resolved = resolveSeasonIdByDate(newDate);
            if (resolved !== null && this.isSeasonAvailable(resolved)) {
                this.seasonId = resolved;
            }
        },
        seasonId(newValue, oldValue) {
            // Only mark as manual when user changes it after an auto-set
            if (oldValue !== null && newValue !== null && newValue !== this.autoSeasonId()) {
                this.seasonManuallyChanged = true;
            }
        },
        seasons() {
            // Once seasons load, try to auto-select based on current gameDate
            if (this.seasonId === null && this.gameDate !== null) {
                const resolved = resolveSeasonIdByDate(this.gameDate);
                if (resolved !== null && this.isSeasonAvailable(resolved)) {
                    this.seasonId = resolved;
                }
            }
        }
    },
    methods: {
        isSeasonAvailable(id) {
            return Array.isArray(this.seasons) && this.seasons.some(s => Number(s.id) === Number(id));
        },
        autoSeasonId() {
            return resolveSeasonIdByDate(this.gameDate);
        },
        createTeamRound() {
            if(this.gameDate === null){
                this.$buefy.snackbar.open({
                    duration: 4000,
                    position: 'is-top',
                    type: 'is-danger',
                    message: `Du mangler at sætte en spilledato`
                })
                return false
            }

            if(this.round === null || this.round === ''){
                this.$buefy.snackbar.open({
                    duration: 4000,
                    position: 'is-top',
                    type: 'is-danger',
                    message: `Du mangler at angive et rundenummer`
                })
                return false
            }

            if(this.seasonId === null){
                this.$buefy.snackbar.open({
                    duration: 4000,
                    position: 'is-top',
                    type: 'is-danger',
                    message: `Du mangler at vælge en sæson`
                })
                return false
            }

            this.loading = true
            const createTeamRoundGQL = gql`
                        mutation ($input: CreateTeamRoundInput!){
                          createTeamRound(input: $input){
                            id
                            name
                            gameDate
                          }
                        }
                    `;
            this.$apollo
                .mutate(
                    {
                        mutation: createTeamRoundGQL,
                        variables: {
                            input: {
                                name: this.name,
                                gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                                version: this.version,
                                round: this.round,
                                seasonId: this.seasonId
                            }
                        }
                    })
                .then(({data}) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-success',
                            message: `Dit hold er gemt`
                        })
                    this.$router.push({name: 'team-fight-edit', params: {teamUUID: data.createTeamRound.id}})
                }).finally(() => {
                this.loading = false
            })
        }
    }
}
</script>

<style scoped>

</style>
