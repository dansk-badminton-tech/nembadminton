<template>
    <ul>
        <li v-for="teamFight in this.teamFights">
            <router-link v-bind:to="'/team-fight/'+teamFight+'/edit'">{{ teamFight }}</router-link>
        </li>
    </ul>
</template>

<script>
export default {
    name: "RecentVisit",
    data() {
        return {
            teamFights: []
        }
    },
    mounted() {
        this.teamFights = JSON.parse(localStorage.getItem('teams-history')) === null
                          ? []
                          : JSON.parse(localStorage.getItem('teams-history'))
        this.$root.$on('edit-enter', (params) => {
            if (this.teamFights.indexOf(params.teamUUID) === -1) {
                this.teamFights.push(params.teamUUID)
            }
            localStorage.setItem('teams-history', JSON.stringify(this.teamFights))
        })
    }
}
</script>

<style scoped>

</style>
