<template>
    <section>
        <b-steps v-model="activeStep">
            <template>
                <b-step-item label="Basis">
                    <b-field label="Klub">
                        <BadmintonPlayerClubs v-model="clubId"/>
                    </b-field>
                </b-step-item>
                <b-step-item label="Holdkampe">
                    <h1 class="title">Rangliste</h1>
                    <h2 class="subtitle">§ 38. Den først offentliggjorte rangliste i en ny måned er gældende for
                        holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende
                        måned. </h2>
                    <b-field>
                        <RankingListDropdown v-model="rankingList" :season="currentSeason"></RankingListDropdown>
                    </b-field>
                    <h1 class="title">Hold kampe</h1>
                    <h2 class="subtitle">Vælge den specifikke hold kamp. Husk ranglisten skal passe med holdkamps
                        runden</h2>
                    <b-field>
                        <Teams v-model="selectedMatches" :club-id="clubId"></Teams>
                    </b-field>
                </b-step-item>
                <b-step-item>
                    <h1 class="title">Hold sortering</h1>
                    <h2 class="subtitle">Sortering er vigtig når spillerunden skal tjekkes. Brug knapperne, så styrkeordenen passer</h2>
                    <SortMatches v-model="selectedMatchesSorted"></SortMatches>
                    <hr/>
                    <b-field>
                        <b-checkbox v-model="sortingConfirmed">Holdene står i den rigtige sortering. (Flyt hold
                            rundt via Drag&Drop eller via knapperne)
                        </b-checkbox>
                    </b-field>
                    <b-button size="is-large mt-2"
                              :disabled="!sortingConfirmed">Tjek spillerunden
                    </b-button>
                </b-step-item>
            </template>
        </b-steps>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../../components/badminton-player/BadmintonPlayerClubs";
import Teams from "./Teams";
import RankingListDropdown from "../../components/ranking-list-dropdown/RankingListDropDown";
import {getCurrentSeason, swapObject} from "../../helpers";
import SortMatches from "./SortMatches";
import gql from "graphql-tag";

export default {
    name: "CheckTeamFight",
    components: {SortMatches, RankingListDropdown, Teams, BadmintonPlayerClubs},
    computed:{
        currentSeason: getCurrentSeason
    },
    watch: {
        selectedMatches: function(val){
            this.selectedMatchesSorted = [...val]
        }
    },
    data(){
        return {
            activeStep: 0,
            clubId: null,
            selectedMatches: [],
            selectedMatchesSorted: [],
            rankingList: null,
            sortingConfirmed: false
        }
    },
    methods: {
        validate(){
            const matchIds = this.selectedMatchesSorted.map(o => o.match.leagueMatchId);
            this.$apollo.query({
                query: gql`
                    query teamMatchesFormattedForValidation($matchIds: [Int!]){
                        teamMatchesFormattedForValidation(matchIds: $matchIds){
                            leagueMatchId
                            name
                            squad {
                                playerLimit
                                league
                                categories {
                                    name
                                    category
                                    players {
                                        name
                                        gender
                                        points {
                                            points
                                            category
                                            id
                                            position
                                            version
                                            vintage
                                        }
                                    }
                                }
                            }
                        }
                    }
                `
            })
        }
    }
}
</script>

<style scoped>

</style>
