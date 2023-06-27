<template>
    <span>
        <b-modal v-model="showLinesModal" :width="940">
            <pre v-if="logs.length">{{ logs.join('\n') }}</pre>
        </b-modal>
    </span>
</template>
<script>
import {findLevel, findPlayersInCategory} from "../../helpers";

export default {
    name: 'ValidateTeams',
    props: {
        teams: Array,
    },
    data() {
        return {
            logs: [],
            showLinesModal: false
        }
    },
    methods: {
        validTeams() {
            this.logs = []
            let limit = 50
            const lowToHighTeams = this.teams.slice().reverse()
            lowToHighTeams.forEach((team, index) => {
                for (let category of team.categories) {
                    for (let player of category.players) {
                        let controlTeams = lowToHighTeams.slice()
                        controlTeams = controlTeams.slice(1 + index, controlTeams.length)
                        for (let checkTeam of controlTeams) {
                            let players = findPlayersInCategory(checkTeam.categories, category.category, player.gender)
                            for (let controlPlayer of players) {
                                let controlPlayerLevel = findLevel(controlPlayer, category.category);
                                let playerLevel = findLevel(player, category.category);
                                if (controlPlayerLevel < playerLevel) {
                                    let playerMinusThreshold = playerLevel - limit;
                                    if (controlPlayerLevel < playerMinusThreshold) {
                                        this.logs.push(category.category + ': ' + player.name + '(' + playerLevel + '-' + limit + ') has higher level then ' + controlPlayer.name + ' (' + controlPlayerLevel + ')')
                                    }
                                }
                            }
                        }
                    }
                }
            })
            if (this.logs.length === 0) {
                this.logs.push('Alle hold er gyldig')
            }
            this.showLinesModal = true
        }
    }
}
</script>
