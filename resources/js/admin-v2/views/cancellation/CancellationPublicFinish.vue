<script>
import TitleBar from "@/components/TitleBar.vue";

export default {
    name: "CancellationPublicFinish" ,
    components: {TitleBar},
    props: {sharingId: String},
    data: () => {
        return {
            titleStack: ['Admin', 'Afbud']
        }
    },
    methods: {
        redirect() {
            this.$router.push({name: 'cancellation-public-view', params: {sharingId: this.sharingId}})
        }
    }
}
</script>

<template>
    <section class="section">
        <h2 class="title is-4">Tak for at registrere afbud 😎🏸</h2>
        <h1 class="subtitle">Vi har sendt en email til din klub samt en kvittering til dig.</h1>
        <b-button type="is-info" @click="redirect" expanded>Opret nyt afbud</b-button>
    </section>
</template>

<style scoped>

</style>
