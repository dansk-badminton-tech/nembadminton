<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Onboarding
        </hero-bar>
        <section class="section is-main-section">
            <div class="mb-5 is-align-content-center has-text-centered">
                <h1 class="title">Importer data fra badmintonplayer.dk... (eta 1-3 min)</h1>
                <progress :value="progress" class="progress is-large is-info" max="100"></progress>
                <h1 v-show="isCompleted" class="title">Importering færdig. Sender dig til dashboard om 3 sekunder</h1>
            </div>
            <ActivityLog/>
        </section>
    </div>
</template>

<script>
import MeQuery from '../../../queries/me.gql'
import ActivityLog from "../common/ActivityLog.vue";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

export default {
    name: "Onboarding",
    components: {HeroBar, TitleBar, ActivityLog},
    inject: ['clubhouseId'],
    computed: {
        isCompleted() {
            return this.me?.club?.initialized;
        },
        progress() {
            return this.me?.club?.initialized
                   ? 100
                   : undefined;
        }
    },
    data: () => {
        return {
            titleStack: ['Admin', 'Onboarding']
        }
    },
    apollo: {
        me: {
            query: MeQuery,
            pollInterval: 2000,
            fetchPolicy: "network-only",
            result({data}) {
                if (data.me.club.initialized) {
                    setTimeout(() => {
                        this.$router.push({name: 'home', params: {clubhouseId: this.clubhouseId}})
                    }, 3000)
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
