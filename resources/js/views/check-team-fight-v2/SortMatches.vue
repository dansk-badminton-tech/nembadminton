<template>
    <b-table :data="matches"
             :draggable="true"
             @dragstart="dragstart"
             @drop="drop"
             @dragover="dragover"
             @dragleave="dragleave">
        <b-table-column
            label="#"
            width="20"
            numeric
            v-slot="props">
            {{ props.index + 1 }}
        </b-table-column>
        <b-table-column
            field="match.divisionName"
            label="Række"
            v-slot="props">
            {{ props.row.match.divisionName }} - {{ props.row.match.groupName }}
        </b-table-column>
        <b-table-column
            field="match.matchTime"
            label="Spille tidspunkt"
            v-slot="props">
            {{ props.row.match.matchTime }}
        </b-table-column>
        <b-table-column
            label="Hjemme"
            v-slot="props">
            {{ props.row.match.teamName1 }}
        </b-table-column>
        <b-table-column
            label="Ude"
            v-slot="props">
            {{ props.row.match.teamName2 }}
        </b-table-column>
        <b-table-column
            v-slot="props">
            <b-button :disabled="props.index === 0" @click="moveUp(props.index)" type="is-success">
                Op
            </b-button>
            <b-button :disabled="props.index === value.length-1" @click="moveDown(props.index)"
                      type="is-success">Ned
            </b-button>
            <b-button tag="a" target="_blank"
                      :href="'https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'+currentSeason+',,,,,'+props.row.match.leagueMatchId+',,'"
                      type="is-success">Se på BP
            </b-button>
        </b-table-column>
    </b-table>
</template>

<script>
import {getCurrentSeason, swap} from "../../helpers";

export default {
    name: "SortMatches",
    props: ['value'],
    computed: {
        currentSeason: getCurrentSeason,
        matches(){
            return this.value
        }
    },
    data() {
        return {
            draggingRow: null,
            draggingRowIndex: null
        }
    },
    methods: {
        moveUp(index) {
            let currentMatches = [...this.matches];
            let from = index;
            let to = from - 1;
            swap(currentMatches, from, to)
            this.$emit('input', currentMatches)
        },
        moveDown(index) {
            let currentMatches = [...this.matches];
            let from = index;
            let to = from + 1;
            swap(currentMatches, from, to)
            this.$emit('input', currentMatches)
        },
        drop(payload) {
            payload.event.target.closest('tr').classList.remove('is-selected')
            const droppedOnRowIndex = payload.index
            const currentMatches = [...this.matches];
            let b = currentMatches[this.draggingRowIndex];
            currentMatches[this.draggingRowIndex] = currentMatches[droppedOnRowIndex];
            currentMatches[droppedOnRowIndex] = b;
            this.$emit('input', currentMatches)
        },
        dragstart(payload) {
            this.draggingRow = payload.row
            this.draggingRowIndex = payload.index
            payload.event.dataTransfer.effectAllowed = 'copy'
        },
        dragover(payload) {
            payload.event.dataTransfer.dropEffect = 'copy'
            payload.event.target.closest('tr').classList.add('is-selected')
            payload.event.preventDefault()
        },
        dragleave(payload) {
            payload.event.target.closest('tr').classList.remove('is-selected')
            payload.event.preventDefault()
        },
    }
}
</script>

<style scoped>

</style>
