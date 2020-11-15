<template>
    <div class="content has-text-grey has-text-centered">
        <p>
            <b-icon
                icon="users"
                size="is-large">
            </b-icon>
        </p>
        <p>Kom i gang med din næste holdkamp planlægning her</p>
        <b-button
            tag="router-link"
            to="/team-fight/create"
            type="is-primary">
            Opret holdkamp
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
