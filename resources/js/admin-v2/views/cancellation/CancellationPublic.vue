<script>
import gql from "graphql-tag";
import _ from "lodash/fp.js";
import TeamFights from "@/views/cancellation/TeamFights.vue";
import {getCurrentSeason} from "@/helpers.js";
import Form from "@/components/Kustomer/Partials/Form.vue";
import Teams from "@/views/cancellation/Teams.vue";
import MemberSearchCancellation from "./MemberSearchCancellation.vue";
import TeamMatchCalendar from "@/views/calendar/TeamMatchCalendar.vue";

export default {
    name: "CancellationPublic",
    props: {"sharingId": String},
    components: {TeamMatchCalendar, Teams, Form, TeamFights, MemberSearchCancellation},
    data() {
        return {
            form: {
                selectedPlayer: {},
                additionalInfo: "",
                email: '',
                selectedDates: []
            },
            selectedTeams: [],
            badmintonPlayerTeamFightsBulk: [],
            cancellationCollectorPublic: {
                clubs: []
            }
        }
    },
    computed: {
        hasSelectedPlayer() {
            return Object.keys(this.form.selectedPlayer).length > 0
        },
        showClubNames() {
            if (this.cancellationCollectorPublic.clubs.length === 0) {
                return "Ingen klubber"
            }
            return this.cancellationCollectorPublic.clubs.map(club => club.name1).join(", ")
        },
        isAtleastOneDateSelected() {
            return this.form.selectedDates.length > 0
        },
        resolveSelectedDatesIntoHtml() {
            return this.form.selectedDates.map(d => '<li>' + d.toISOString().substring(0, 10) + '</li>').join("")
        },
        convertToEvents() {
            const dateObjects = []
            for (let date of this.form.selectedDates) {
                dateObjects.push({
                                     startDate: date,
                                     endDate: date,
                                     title: 'Dit afbud',
                                     classes: 'has-background-danger'
                                 })
            }
            return dateObjects;
        }

    },
    apollo: {
        cancellationCollectorPublic: {
            query: gql`
                query cancellationCollectorPublic($sharingId: String!){
                    cancellationCollectorPublic(sharingId: $sharingId){
                        id
                        clubs {
                            id
                            name1
                        }
                    }
                }
                `,
            variables() {
                return {
                    sharingId: this.sharingId
                }
            },
            error(err) {
                this.$buefy.toast.open({message: `Error fetching cancellation collector`, type: "is-danger", duration: 5000});
            }
        },
        badmintonPlayerTeamFightsBulk: {
            query: gql`
                query badmintonPlayerTeamFightsBulk($input: [BadmintonPlayerTeamFightsInput]){
                                badmintonPlayerTeamFightsBulk(input: $input){
                                    gameTime
                                    matchId
                                    round
                                    roundDate
                                    teams
                                }
            }`,
            variables() {
                return {
                    input: this.selectedTeams.map(t => ({
                        season: getCurrentSeason(),
                        clubId: t.clubId,
                        ageGroupId: Number(t.ageGroupId),
                        leagueGroupId: Number(t.leagueGroupId),
                        clubName: t.name
                    }))
                }
            },
            error(err) {
                this.$buefy.toast.open({message: `Error fetching teams rounds`, type: "is-danger", duration: 5000});
            }
        }
    },
    methods: {
        resetForm() {
            this.form = {
                selectedPlayer: {},
                additionalInfo: "",
                email: '',
                selectedDates: []
            }
        },
        createCancellation() {
            this.sendingCancellation = true;
            this.$apollo.mutate(
                {
                    mutation: gql`
                                        mutation createCancellation($input: CancellationInput!){
                                            createCancellation(input: $input){
                                                id
                                                refId
                                            }
                                        }
                                    `,
                    variables: {
                        input: {
                            refId: this.form.selectedPlayer.refId,
                            message: this.form.additionalInfo,
                            cancellationCollector: {
                                connect: this.cancellationCollectorPublic.id
                            },
                            dates: {
                                create: this.form.selectedDates.map(d => ({date: d.toISOString().substring(0, 10)}))
                            }
                        }
                    }
                })
                .then((data) => {
                    this.$buefy.toast.open({message: 'Afbud sendt!', type: "is-success", duration: 5000})
                    this.resetForm();
                })
                .catch((err) => {
                    this.$buefy.toast.open({message: `Fejl kunne ikke sende afbud`, type: "is-danger", duration: 5000});
                })
                .finally(() => {
                    this.sendingCancellation = false;
                })
        },
        confirmCancellation() {
            if (this.isAtleastOneDateSelected) {
                const resolveSelectedDatesIntoHtml = this.resolveSelectedDatesIntoHtml;
                this.$buefy.dialog.confirm({
                                               title: 'Afbud for ' + this.form.selectedPlayer.name,
                                               message: `<div class="content">Du har meldt afbud på følgende datoer:
                                               <ul>
                                                ${resolveSelectedDatesIntoHtml}
                                               </ul>
                                               <hr>
                                               Besked: ${this.form.additionalInfo || 'Ingen besked'}
                                               </div>`,
                                               cancelText: 'Tilbage',
                                               confirmText: 'Send afbud',
                                               type: 'is-success',
                                               onConfirm: () => {
                                                   this.createCancellation()
                                               }
                                           })
            } else {
                this.$buefy.toast.open({message: `Du skal vælge mindst 1 dato`, type: "is-danger", duration: 5000});
            }
        }
    },
}

</script>

<template>
    <section>
        <h2 class="title is-4">Afbud for {{ showClubNames }}</h2>
        <b-loading v-model="$apollo.queries.badmintonPlayerTeamFightsBulk.loading"></b-loading>
        <form @submit.prevent="confirmCancellation">
            <b-field label="Vælge dit navn" message="Søg efter dit navn fra badmintonplayer">
                <MemberSearchCancellation v-model="form.selectedPlayer" :clubs="cancellationCollectorPublic.clubs"></MemberSearchCancellation>
            </b-field>
            <b-field label="Dit email" message="Bruges til at sende en kvittering">
                <b-input placeholder="badminton@badminton.dk" type="email" v-model="form.email" required/>
            </b-field>
            <b-field label="Vælge afbuds datoer" message="Vælge mindst 1 dato. Er kan vælge mere end 1 dato">
                <b-datepicker
                    placeholder="Vælge datoer du ikke kan spille..."
                    v-model="form.selectedDates"
                    :first-day-of-week="1"
                    multiple
                    required>
                </b-datepicker>
            </b-field>
            <b-field label="Besked med afbudet. Valgfrit">
                <b-input type="textarea" maxlength="200" v-model="form.additionalInfo" placeholder="Besked sammen med afbud"/>
            </b-field>
            <h2 class="title is-4">Holdkamp kalender (Beta)</h2>
            <h2 class="subtitle">Viser alle holdkampe for senior. Dage som du har meldt afbud på markeres med rød</h2>
            <TeamMatchCalendar :selected-dates="convertToEvents" :clubs="cancellationCollectorPublic.clubs"/>
            <b-button native-type="submit" class="mt-4" expanded size="is-medium">Meld afbud</b-button>
        </form>
    </section>
</template>
