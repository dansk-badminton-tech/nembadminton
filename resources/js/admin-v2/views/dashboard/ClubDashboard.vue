<template>
    <div dusk="club-dashboard-page">
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            {{ me?.clubhouse.name }}
        </hero-bar>
        <section class="section is-main-section">
            <UpcomingTeamRounds class="mb-5"/>
            <RecentCancellations :collector-id="collectorId" class="mb-5"/>
            <SyncHealthBadge/>
        </section>
    </div>
</template>

<script>

import MeQuery from '../../../queries/me.gql'
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import SyncHealthBadge from "@/views/dashboard/SyncHealthBadge.vue";
import RecentCancellations from "@/views/dashboard/RecentCancellations.vue";
import UpcomingTeamRounds from "@/views/dashboard/UpcomingTeamRounds.vue";

export default {
    name: "ClubDashboard",
    inject: ['clubhouseId'],
    components: {UpcomingTeamRounds, SyncHealthBadge, RecentCancellations, HeroBar, TitleBar},
    apollo: {
        me: {
            query: MeQuery,
        },
    },
    data() {
        return {
            titleStack: ['Admin', 'Dashboard'],
        }
    },
    computed: {
        collectorId() {
            const id = this.me?.clubhouse?.cancellationCollector?.id
            return id != null ? String(id) : null
        }
    }
}
</script>
