<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            Holdrunde
        </hero-bar>
        <section class="section is-main-section">
            <div class="mb-3 mt-3">
                <b-button :to="{name: 'team-fight-create'}" type="is-link" icon-left="plus" tag="router-link">Opret holdrunde</b-button>
            </div>
            <card-component
                title="Holdrunde"
                class="has-table has-mobile-sort-spaced"
            >
                <ListTeamFights />
            </card-component>
        </section>
    </div>
</template>

<script>
import gql from "graphql-tag"
import ListTeamFights from "./ListTeamFights.vue";
import CreateTeamFightAction from "./CreateTeamFightAction.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import CardComponent from "../../components/CardComponent.vue";

export default {
    name: "TeamFightList",
    components: {CardComponent, HeroBar, TitleBar, ListTeamFights, CreateTeamFightAction},
    data() {
        return {
            titleStack: ['Admin', 'Holdrunde']
        }
    }
}
</script>

