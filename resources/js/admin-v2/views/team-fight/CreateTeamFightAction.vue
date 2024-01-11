<template>
    <div class="box content has-text-grey has-text-centered">
        <p>
            <b-icon
                icon="account-group"
                size="is-large">
            </b-icon>
        </p>
        <p>Kom i gang med din næste holdrunde planlægning her</p>
        <b-button
            tag="router-link"
            to="/team-fight/create"
            type="is-primary">
            Opret holdrunde
        </b-button>
    </div>
</template>

<script>
export default {
    name: "CreateTeamFightAction",
    data() {
        return {
            teamFights: []
        }
    },
    mounted() {

    }
}
</script>

<style scoped>

</style>
