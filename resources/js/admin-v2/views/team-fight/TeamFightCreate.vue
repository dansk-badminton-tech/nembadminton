<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Opret holdrunde
        </hero-bar>
        <section class="section is-main-section">
            <form @submit.prevent="createTeam" v-if="!$apollo.queries.me.loading" class="column">
                <b-field label="Navn">
                    <b-input v-model="name" required placeholder="fx. Runde 1"></b-input>
                </b-field>
                <b-field label="Spilledato">
                    <b-datepicker
                        v-model="gameDate"
                        icon="calendar"
                        placeholder="Klik for at vælge dato..."
                        locale="da-DK"
                        :first-day-of-week="1"
                        trap-focus>
                    </b-datepicker>
                </b-field>
                <b-field label="Rangliste">
                    <RankingVersionSelect required v-model="version" :playing-date="gameDate" expanded/>
                </b-field>
                <b-button class="mt-2" native-type="submit" label="Opret" icon-left="plus" :loading="loading" />
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
        createTeam() {
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
            const createTeamGQL = gql`
                        mutation ($input: CreateTeamInput!){
                          createTeam(input: $input){
                            id
                            name
                            gameDate
                          }
                        }
                    `;
            this.$apollo
                .mutate(
                    {
                        mutation: createTeamGQL,
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
                    this.$router.push({name: 'team-fight-edit', params: {teamUUID: data.createTeam.id}})
                }).finally(() => {
                this.loading = false
            })
        }
    }
}
</script>

<style scoped>

</style>
