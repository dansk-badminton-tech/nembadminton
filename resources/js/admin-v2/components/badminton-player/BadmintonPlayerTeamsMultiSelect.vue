<template>
    <b-table
        ref="table"
        v-model:checked-rows="selectedTeams"
        :columns="columns"
        :data="badmintonPlayerTeams"
        :loading="$apollo.queries.badmintonPlayerTeams.loading"
        :checkable="checkable"
        :is-row-checkable="isRowCheckable"
        :detailed="detailed"
        :detail-key="detailKey"
        :show-detail-icon="showDetailIcon"
        :hoverable="true"
        @click="openDetail"
    >
        <template #empty>
            <div class="has-text-centered">Ingen hold fundet. Har du valgt den rigtige sæson og klub?</div>
        </template>
        <template #detail="props">
            <slot name="detail-body" v-bind="props"></slot>
        </template>
    </b-table>
</template>

<script>
import gql from "graphql-tag"
import BadmintonPlayerTeamFights from "./BadmintonPlayerTeamFights.vue";
import badmintonPlayerTeams from "@/components/badminton-player/BadmintonPlayerTeams.vue";

export default {
    name: "BadmintonPlayerTeamsMultiSelect",
    components: {BadmintonPlayerTeamFights},
    emits: ['update:modelValue'],
    props: {
        'modelValue': Array,
        'clubId': Number,
        'season': Number,
        'checkable': {
            type: Boolean,
            default: true
        },
        'detailed': {
            type: Boolean,
            default: false
        },
        'detailKey': String,
        showDetailIcon: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        isRowCheckable(row) {
            return !((new RegExp('u[0-9]+', 'gmi')).test(row.league)
                     || (new RegExp('sen\\+[0-9]+', 'gmi')).test(row.league)
                     || (new RegExp('senior motion', 'gmi')).test(row.league)
                     || (new RegExp('DMU', 'gmi')).test(row.league)
                     || (new RegExp('4 spillere', 'gmi')).test(row.league)
            )
        },
        openDetail(row){
            if(this.detailed){
                this.$refs.table.toggleDetails(row)
            }
        }
    },
    watch: {
        clubId(newValue, oldValue){
            if(newValue !== oldValue){
              this.selectedTeams = []
            }
        },
        selectedTeams(){
            this.$emit('update:modelValue', this.selectedTeams)
        }
    },
    data() {
        return {
            columns: [
                {
                    field: 'name',
                    label: 'Navn',
                },
                {
                    field: 'league',
                    label: 'Række',
                }
            ],
            selectedTeams: [],
            badmintonPlayerTeams: [],
        }
    },
    apollo: {
        badmintonPlayerTeams: {
            query: gql`
                query($input: BadmintonPlayerTeamsInput){
                    badmintonPlayerTeams(input: $input){
                        leagueGroupId
                        ageGroupId
                        name
                        league
                    }
                }
            `,
            result(ApolloQueryResult, key) {

            },
            variables() {
                return {
                    input: {
                        clubId: this.clubId,
                        season: parseInt(this.season)
                    }
                }
            },
            skip() {
                return this.clubId === null || this.season === null
            }
        },
    }
}
</script>

<style scoped>

</style>
