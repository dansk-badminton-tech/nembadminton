<template>
    <div>
        <div class="mb-5 is-align-content-center has-text-centered">
            <h1 class="title">Importer data fra badmintonplayer.dk... (eta 1-3 min)</h1>
            <progress :value="progress" class="progress is-large is-info" max="100"></progress>
            <h1 v-show="isCompleted" class="title">Importering færdig. Sender dig til dashboard om 3 sekunder</h1>
        </div>
        <ActivityLog/>
    </div>
</template>

<script>
import MeQuery from '../../queries/me.gql'
import ActivityLog from "../ActivityLog";

export default {
    name: "Onboarding",
    components: {ActivityLog},
    computed: {
        isCompleted(){
            return this.me?.club?.initialized;
        },
        progress(){
            return this.me?.club?.initialized ? 100 : undefined;
        }
    },
    apollo: {
        me: {
            query: MeQuery,
            pollInterval: 2000,
            fetchPolicy: "network-only",
            result({data}){
                if(data.me.club.initialized){
                    setTimeout(() => {
                        this.$router.push({name: 'my-club'})
                    }, 3000)
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
