<template>
    <fragment>
        <div class="column is-one-third" v-for="(fights,key) in groupByRound">
            <div class="box">
                Runde {{key}}
                <hr class="pt-0">
                <b-field :key="fight.matchId" v-for="fight in fights">
                    <b-checkbox v-model="selectedTeamMatch" :native-value="fight">
                        {{fight.gameTime}}:
                        {{fight.teams.slice(0,2).join(' VS ')}}
                    </b-checkbox>
                </b-field>
            </div>
        </div>
    </fragment>
</template>
<script>
import gql from "graphql-tag";

import groupBy from "lodash/groupBy.js";

export default {
    name: 'TeamFights',
    props: {"data": Array, 'value': Array},
    data(){
        return {
            selectedTeamMatch: []
        }
    },
    watch:{
        selectedTeamMatch(newValue){
            this.$emit('input', newValue)
        }
    },
    computed: {
        groupByRound(){
            return groupBy(this.data, d => d.round)
        }
    }
}
</script>

<style>

</style>
