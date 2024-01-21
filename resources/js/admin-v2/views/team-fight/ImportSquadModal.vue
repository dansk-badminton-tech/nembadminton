<script>

import BadmintonPlayerTeamsMultiSelect from "../../components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue";
import MeQuery from "../../../queries/me.gql";
import {getCurrentSeason} from "../../helpers";
import OptionalRanking from "../check-team-fight/OptionalRanking.vue";
import BadmintonPlayerTeamFights from "../../components/badminton-player/BadmintonPlayerTeamFights.vue";

export default {
    name: "ImportSquadModal",
    components: {BadmintonPlayerTeamFights, OptionalRanking, BadmintonPlayerTeamsMultiSelect},
    props: ['addSquad'],
    data() {
        return {
            isModalActive: false,
            loading: false,
            playerTeams: [],
            selectedTeamMatches: []
        };
    },
    methods: {
        submitSquad() {

        },
        addSquadFromBP(teamFight){
            this.addSquad()
            console.log(teamFight)
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
    <form @submit.prevent="submitSquad">
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Import og tilknyt</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <div class="content">
                    <p>Valideringen af holdet foretages på basis af DH-reglementet. Det er værd at bemærke, at der ikke bliver taget hensyn til eventuelle unikke regler for hold, der befinder sig under Danmarksserien.</p>
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
                                                        <b-button @click="addSquadFromBP(teamFight)" type="is-primary" label="Tilføj"/>
                                                    </p>
                                                </b-field>
                                            </template>
                                        </BadmintonPlayerTeamFights>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </BadmintonPlayerTeamsMultiSelect>
                </div>
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
