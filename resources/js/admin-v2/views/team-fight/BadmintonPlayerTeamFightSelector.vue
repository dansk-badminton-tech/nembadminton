<script>
import BadmintonPlayerTeamFights from "../../components/badminton-player/BadmintonPlayerTeamFights.vue";
import BadmintonPlayerTeamsMultiSelect from "../../components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue";
import {getCurrentSeason} from "../../helpers";
import MeQuery from "../../../queries/me.gql";

export default {
    name: "BadmintonPlayerTeamFightSelector",
    components: {BadmintonPlayerTeamsMultiSelect, BadmintonPlayerTeamFights},
    props: {
        importInformation: Function
    },
    data(){
        return {
            playerTeams: [],
            selectedTeamMatches: []
        }
    },
    computed: {
        getCurrentSeason,
        clubId() {
            if (this.$apollo.loading) {
                return 0
            }
            return parseInt(this.me?.club?.id)
        }
    },
    apollo: {
        me: {
            query: MeQuery,
        }
    }
}
</script>

<template>
    <BadmintonPlayerTeamsMultiSelect v-model="playerTeams" :clubId="clubId" :season="getCurrentSeason" :checkable="false" detail-key="league" detailed show-detail-icon>
        <template v-slot:detail-body="props">
            <article class="media">
                <div class="media-content">
                    <div class="content">
                        <BadmintonPlayerTeamFights v-model="selectedTeamMatches[props.index]" :clubId="clubId" :player-team="props.row" :season="getCurrentSeason">
                            <template v-slot:default="props">
                                <b-field :loading="props.loading" rounded :key="teamFight.matchId" v-for="teamFight in props.teamFights">
                                    <b-input
                                        expanded
                                        readonly
                                        :value="teamFight.round + ' - ' + teamFight.gameTime +' - '+ teamFight.teams.join(' - ')"
                                        type="text">
                                    </b-input>
                                    <p class="control">
                                        <b-button @click="importInformation(teamFight, props.playerTeam)" type="is-primary" label="Import"/>
                                    </p>
                                    <p class="control">
                                        <b-button icon-right="open-in-new" tag="a" target="_blank"
                                                  :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+getCurrentSeason+',,,,,'+teamFight.matchId+',,'"
                                                  type="is-link">Se på BP
                                        </b-button>
                                    </p>
                                </b-field>
                            </template>
                        </BadmintonPlayerTeamFights>
                    </div>
                </div>
            </article>
        </template>
    </BadmintonPlayerTeamsMultiSelect>
</template>

<style scoped>

</style>
