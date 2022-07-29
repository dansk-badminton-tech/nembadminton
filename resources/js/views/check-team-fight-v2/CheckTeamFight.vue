<template>
    <section>
        <b-loading v-model="isLoading"></b-loading>
        <b-steps v-if="teamMatchesFormattedForValidation === null" v-model="activeStep">
            <template>
                <b-step-item label="Basis">
                    <b-field label="Klub">
                        <BadmintonPlayerClubs @input="nextStep" v-model="clubId"/>
                    </b-field>
                    <article class="message is-info">
                        <div class="message-header">
                            <p>Begrænsninger (indtil videre)</p>
                        </div>
                        <div class="content message-body">
                            <ul>
                                <li>Viser kun holdkampe som er maximum 1 måned</li>
                                <li>Du kan kun tjekke hold fra danmarksserien og op (Der arbejds på at få alle senior rækker med)</li>
                                <li>Kun en rangliste, så holdkampesrunder som overlapper med to ranglister er ikke understøttet endnu.</li>
                            </ul>
                        </div>
                    </article>
                </b-step-item>
                <b-step-item label="Hold">
                    <h1 class="title">Hold</h1>
                    <h2 class="subtitle">Vælge hvilke hold som skal være med i spillerunden.</h2>
                    <TeamsSelector v-model="selectedTeams" :club-id="clubId"/>
                </b-step-item>
                <b-step-item label="Holdkampe">
                    <h1 class="title">Rangliste</h1>
                    <h2 class="subtitle">§ 38. Den først offentliggjorte rangliste i en ny måned er gældende for
                        holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende
                        måned. </h2>
                    <b-field>
                        <RankingListDropdown v-model="rankingList" :season="currentSeason"></RankingListDropdown>
                    </b-field>
                    <div>
                        <h1 class="title">Hold kampe (14 dage tilbage)</h1>
                        <h2 class="subtitle">Vælge den specifikke hold kamp. Husk ranglisten skal passe med holdkamps
                            runden</h2>
                        <b-field>
                            <Teams v-model="selectedMatches" :teams="selectedTeams"></Teams>
                        </b-field>
                    </div>
                </b-step-item>
                <b-step-item label="Bekræft">
                    <h1 class="title">Hold sortering</h1>
                    <h2 class="subtitle">Sortering er vigtig når spillerunden skal tjekkes. Brug knapperne, så
                        styrkeordenen passer</h2>
                    <SortMatches v-model="selectedMatches"></SortMatches>
                    <hr/>
                    <b-field>
                        <b-checkbox v-model="sortingConfirmed">Holdene står i den rigtige sortering. (Flyt hold
                            rundt via Drag&Drop eller via knapperne)
                        </b-checkbox>
                    </b-field>
                    <b-button size="is-large mt-2" :disabled="!sortingConfirmed" @click="getTeamsMatches">
                        Tjek spillerunden
                    </b-button>
                </b-step-item>
            </template>
        </b-steps>
        <ValidateAndShow v-if="teamMatchesFormattedForValidation !== null"
                         :team-matches="teamMatchesFormattedForValidation"></ValidateAndShow>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../../components/badminton-player/BadmintonPlayerClubs";
import {getCurrentSeason} from "../../helpers";
import SortMatches from "./SortMatches";
import gql from "graphql-tag";
import TeamsSelector from "./TeamsSelecter";
import RankingListDropdown from "../../components/ranking-list-dropdown/RankingListDropDown";
import Teams from "./Teams";
import ValidateAndShow from "./ValidateAndShow";

export default {
    name: "CheckTeamFight",
    components: {ValidateAndShow, Teams, RankingListDropdown, TeamsSelector, SortMatches, BadmintonPlayerClubs},
    computed: {
        currentSeason: getCurrentSeason
    },
    watch: {
        selectedMatches: function (val) {
            this.selectedMatchesSorted = [...val]
        }
    },
    data() {
        return {
            isLoading: false,
            activeStep: 0,
            clubId: null,
            selectedTeams: [],
            selectedMatches: [],
            selectedMatchesSorted: [],
            teamMatchesFormattedForValidation: null,
            rankingList: null,
            sortingConfirmed: false
        }
    },
    methods: {
        nextStep(){
            this.activeStep += 1;
        },
        getTeamsMatches() {
            const matchIds = this.selectedMatchesSorted.map(o => o.match.leagueMatchId);
            this.isLoading = true;
            this.$apollo.query({
                query: gql`
                    query teamMatchesFormattedForValidation($input: TeamMatchesFormattedForValidationInput!){
                        teamMatchesFormattedForValidation(input: $input){
                            name
                            leagueMatchId
                            squad {
                                playerLimit
                                league
                                categories {
                                    name
                                    category
                                    players {
                                        name
                                        gender
                                        refId
                                        points {
                                            points
                                            category
                                            position
                                            version
                                            vintage
                                        }
                                    }
                                }
                            }
                        }
                    }
                `,
                variables: {
                    input: {
                        matchIds,
                        clubId: this.clubId,
                        version: this.rankingList
                    }
                }
            }).then(({data}) => {
                this.teamMatchesFormattedForValidation = data.teamMatchesFormattedForValidation
            }).catch(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 4000,
                        type: 'is-danger',
                        message: `Kunne ikke hente holdkampene for klubben`
                    })
            }).finally(() => {
                this.isLoading = false
            })
        }
    }
}
</script>

