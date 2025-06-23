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
            :to="{name: 'team-fight-create'}"
            type="is-link">
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
