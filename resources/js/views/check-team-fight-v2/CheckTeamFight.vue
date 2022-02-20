<template>
    <section>
        <b-steps v-model="activeStep">
            <template>
                <b-step-item label="Basis">
                    <b-field label="Klub">
                        <BadmintonPlayerClubs v-model="clubId"/>
                    </b-field>
                </b-step-item>
                <b-step-item label="Holdkampe">
                    <b-field label="Hold">
                        <Teams :club-id="clubId"></Teams>
                    </b-field>
                </b-step-item>
            </template>
        </b-steps>
    </section>
</template>

<script>
import BadmintonPlayerClubs from "../../components/badminton-player/BadmintonPlayerClubs";
import Teams from "./Teams";

export default {
    name: "CheckTeamFight",
    components: {Teams, BadmintonPlayerClubs},
    data(){
        return {
            activeStep: 0,
            clubId: null
        }
    }
}
</script>

<style scoped>

</style>
