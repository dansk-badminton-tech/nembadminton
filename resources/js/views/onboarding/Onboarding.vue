<template>
    <div class="is-align-content-center has-text-centered">
        <h1 class="title">Importer data fra badmintonplayer.dk... (eta 1 min)</h1>
        <progress :value="progress" class="progress is-large is-info" max="100"></progress>
        <h1 v-show="isCompleted" class="title">Importering færdig... vent</h1>
    </div>
</template>

<script>
import MeQuery from '../../queries/me.gql'

export default {
    name: "Onboarding",
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
            result({data}){
                if(data.me.club.initialized){
                    setTimeout(() => {
                        this.$router.push({name: 'my-club'})
                    }, 4000)
                }
            }
        }
    }
}
</script>

<style scoped>

</style>
