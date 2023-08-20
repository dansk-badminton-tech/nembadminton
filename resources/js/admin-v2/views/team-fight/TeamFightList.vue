<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            Holdkamp
        </hero-bar>
        <section class="section is-main-section">
        <b-button :to="{name: 'team-fight-create'}" icon-left="plus" tag="router-link">Opret holdkamp</b-button>
        <ListTeamFights />
        </section>
    </div>
</template>

<script>
import gql from "graphql-tag"
import ListTeamFights from "./ListTeamFights.vue";
import CreateTeamFightAction from "./CreateTeamFightAction.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

export default {
    name: "TeamFightList",
    components: {HeroBar, TitleBar, ListTeamFights, CreateTeamFightAction},
    data() {
        return {
            titleStack: ['Admin', 'Holdkamp']
        }
    }
}
</script>

