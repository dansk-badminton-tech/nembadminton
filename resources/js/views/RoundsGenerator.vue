<template>
    <div>
        <div class="columns">
            <div class="column">
                <ClubSearch :select-club="selectClub"></ClubSearch>
            </div>
            <div class="column">
                <PlayerSearch :add-player="addPlayer" :club-id="clubId" :exclude-players="this.players"></PlayerSearch>
            </div>
        </div>
        <b-button @click="addCourt">Tilføj Bane</b-button>
        <div class="mt-3">
            <Court v-for="court in courts" :key="court.id" :court="court" :remove-player="removePlayer"></Court>
        </div>
    </div>
</template>

<script>
import PlayerSearch from "../components/search-player/PlayerSearch";
import ClubSearch from "../components/search-club/ClubSearch";
import Court from "../components/courts/Court";
import PlayerList from "./PlayerList";
import {chunk, defaultIfUndefined} from "../helpers";

export default {
    name: "RoundsGenerator",
    components: {
        PlayerList,
        Court,
        ClubSearch,
        PlayerSearch
    },
    data() {
        return {
            clubId: null,
            courts: [],
            players: [],
            onCourtPlayers: []
        }
    },
    methods: {
        selectClub(id) {
            this.clubId = id
        },
        addPlayer(player) {
            this.players.push(player)
            this.shufflePlayers()
        },
        shufflePlayers() {
            let teams = chunk(this.players, 4)
            for (let court of this.courts) {
                let team = teams.shift();
                court.left = []
                court.right = []
                court.left.push(defaultIfUndefined(team.shift(), {id: 1, name: '-'}))
                court.right.push(defaultIfUndefined(team.shift(), {id: 2, name: '-'}))
                court.left.push(defaultIfUndefined(team.shift(), {id: 3, name: '-'}))
                court.right.push(defaultIfUndefined(team.shift(), {id: 4, name: '-'}))
            }
        },
        removePlayer(court, player) {
            let indexOfCourt = this.courts.indexOf(court);
            let foundCourt = this.courts[indexOfCourt]

            let indexLeft = foundCourt.left.indexOf(player)
            if (indexLeft > -1) {
                foundCourt.left[indexLeft] = {id: indexLeft, name: '-'}
            }
            let indexRight = foundCourt.right.indexOf(player)
            if (indexRight > -1) {
                foundCourt.left[indexRight] = {id: indexLeft, name: '-'}
            }
            this.courts[indexOfCourt] = foundCourt
            console.log(this.courts)
        },
        addCourt() {
            if (typeof this.addCourt.count == 'undefined') {
                this.addCourt.count = 0;
            }
            this.courts.push(
                {
                    id: this.addCourt.count,
                    left: [
                        {id: 1, name: '-'},
                        {id: 2, name: '-'}
                    ],
                    right: [
                        {id: 3, name: '-'},
                        {id: 4, name: '-'}
                    ]
                }
            )
            this.addCourt.count++
        }
    }
}
</script>

