<template>
    <div dusk="team-fight-create-page">
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Opret holdrunde
        </hero-bar>
        <section class="section is-main-section">
            <form @submit.prevent="createTeamRound" v-if="!$apollo.queries.me.loading" class="column">
                <b-field label="Navn">
                    <b-input dusk="team-fight-name-input" v-model="name" required placeholder="fx. Runde 1"></b-input>
                </b-field>
                <b-field label="Spilledato">
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
                <b-button dusk="team-fight-submit-button" class="mt-2" native-type="submit" label="Opret" icon-left="plus" :loading="loading" />
            </form>
        </section>
    </div>
</template>

<script>
import gql from 'graphql-tag'
import ME from "../../../queries/me.gql";
import RankingListDatePicker from "../common/RankingListDatePicker.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

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
            loading: false
        }
    },
    apollo: {
        me: {
            query: ME,
            result({data}) {
                this.clubId = data.me.clubhouse.clubs[0].id
            }
        }
    },
    methods: {
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

            this.loading = true
            const createTeamRoundGQL = gql`
                        mutation ($input: CreateTeamInput!){
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
                                version: this.version
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
