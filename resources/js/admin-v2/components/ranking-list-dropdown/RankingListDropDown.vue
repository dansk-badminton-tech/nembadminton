<template>
    <b-select expanded placeholder="Vælge rangliste" @input="handleInput">
        <option
            v-for="version in rankingVersions"
            :key="version"
            :value="version">
            {{ timeToMonth(version) }}
        </option>
    </b-select>
</template>

<script>
import gql from "graphql-tag";
import {timeToMonth} from "../../views/team-fight/helper";

export default {
    name: 'RankingListDropdown',
    props: ['value', 'season', 'useSystemRankings'],
    methods: {
        timeToMonth,
        handleInput(value) {
            this.$emit('input', value)
        }
    },
    apollo: {
        rankingVersions: {
            query(){
                if(this.useSystemRankings){
                    return gql`
                        query rankingVersionsBP{
                            rankingVersionsBP
                        }
                    `
                }else{
                    return gql`
                        query rankingVersionsApi{
                            rankingVersionsApi
                        }
                    `
                }
            },
            update: data => data.rankingVersionsBP || data.rankingVersionsApi,
        }
    }
};
</script>
