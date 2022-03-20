<template>
    <div>
        <b-field v-for="(team, index) in teams" :key="team.divisionName"
                 :label="team.divisionName">
            <b-select v-model="value[index]" placeholder="Kampe" expanded>
                <option :value="lineup" v-for="(lineup, index) in team.lineups">
                    {{index+1}} {{lineup.match.matchTime}} - {{lineup.match.teamName1}} - {{lineup.match.teamName2}}
                </option>
            </b-select>
        </b-field>
    </div>
</template>

<script>

export default {
    name: "Teams",
    props: {
        value: Array,
        teams: Array
    }
}
</script>

<style scoped>

</style>
