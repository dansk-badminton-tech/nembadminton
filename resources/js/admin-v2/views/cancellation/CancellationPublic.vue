<script>
import gql from "graphql-tag";
import TeamFights from "@/views/cancellation/TeamFights.vue";
import {getCurrentSeason} from "@/helpers.js";
import Teams from "@/views/cancellation/Teams.vue";
import MemberSearchCancellation from "./MemberSearchCancellation.vue";
import TeamMatchCalendar from "@/views/calendar/TeamMatchCalendar.vue";

export default {
    name: "CancellationPublic",
    props: {"sharingId": String},
    components: {TeamMatchCalendar, Teams, TeamFights, MemberSearchCancellation},
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
            },
            sendingCancellation: false
        }
    },
    computed: {
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
            return this.form.selectedDates.map(d => '<li>' + this.formatDateForDisplay(d) + '</li>').join("")
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
        formatDateForDisplay(date) {
            // Format date for display in Danish locale (YYYY-MM-DD)
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        formatDateForServer(date) {
            // Format date for server submission (YYYY-MM-DD in local timezone)
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
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
                            email: this.form.email,
                            cancellationCollector: {
                                connect: this.cancellationCollectorPublic.id
                            },
                            dates: {
                                create: this.form.selectedDates.map(d => ({date: this.formatDateForServer(d)}))
                            }
                        }
                    }
                })
                .then((data) => {
                    this.$buefy.toast.open({message: 'Afbud sendt!', type: "is-success", duration: 5000})
                    this.$router.push({ name: 'cancellation-public-finish' })
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
            if(this.form.selectedPlayer === null || !this.form.selectedPlayer.hasOwnProperty('name')){
                this.$buefy.toast.open({message: `Du skal vælge en spiller`, type: "is-danger", duration: 5000});
                return
            }
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
        <h2 class="title is-4">Meld afbud for {{ showClubNames }}</h2>
        <b-loading v-model="$apollo.queries.badmintonPlayerTeamFightsBulk.loading"></b-loading>
        <form @submit.prevent="confirmCancellation">
            <b-field label="Vælg spiller" message="Der kan kun søges på spillere fra badmintonplayer.dk.">
                <MemberSearchCancellation v-model="form.selectedPlayer" :clubs="cancellationCollectorPublic.clubs"></MemberSearchCancellation>
            </b-field>
            <b-field label="Din email" message="Hertil sendes en kvittering.">
                <b-input placeholder="badminton@badminton.dk" type="email" v-model="form.email" required/>
            </b-field>
            <b-field label="Vælg afbudsdatoer" message="Vælg mindst én dato. Der kan vælges flere datoer.">
                <b-datepicker
                    placeholder="Angiv datoer"
                    v-model="form.selectedDates"
                    :first-day-of-week="1"
                    multiple
                    required
                    :mobile-native="false"
                    locale="da-DK"
                    ref="datepicker"
                >
                    <b-button
                        label="Ryd"
                        type="is-danger"
                        icon-left="close"
                        outlined
                        @click="form.selectedDates = []" />
                    <b-button
                        class="is-pulled-right"
                        label="Gem"
                        type="is-info"
                        icon-left="content-save"
                        @click="$refs.datepicker.toggle()" />

                </b-datepicker>
            </b-field>
            <b-field label="Besked (Valgfrit)">
                <b-input type="textarea" maxlength="200" v-model="form.additionalInfo" placeholder=""/>
            </b-field>
            <b-button :loading="sendingCancellation" type="is-info" native-type="submit" class="mb-4" expanded size="is-medium">Meld afbud</b-button>
            <h2 class="title is-4">Se din afbud i kalenderen</h2>
            <h2 class="subtitle">Dine afbudsdatoer markeres med rød.</h2>
            <TeamMatchCalendar :selected-dates="convertToEvents" :clubs="cancellationCollectorPublic.clubs"/>
        </form>
    </section>
</template>
