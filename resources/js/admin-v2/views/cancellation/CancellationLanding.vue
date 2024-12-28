<script>
import TitleBar from "@/components/TitleBar.vue";

export default {
    name: "CancellationLanding" ,
    components: {TitleBar},
    data: () => {
        return {
            titleStack: ['Admin', 'Afbud']
        }
    },
    methods: {
        redirect() {
            this.$router.push({name: 'cancellation-create'})
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <section class="section is-main-section">
            <h1 class="title">Sådan fungerer det:</h1>
            <div class="columns is-multiline">
                <div class="column is-one-third">
                    <div class="box">
                        <h1 class="title is-4">1. Opsætning</h1>
                        <p>Angiv hvilke(n) klub(ber) du vil indsamle afbud fra.</p>
                    </div>
                </div>
                <div class="column is-one-third">
                    <div class="box">
                        <h1 class="title is-4">2. Del afbudslink</h1>
                        <p>Del afbudslinket med alle spillere i klubben, så de har mulighed for at melde afbud på datoer, hvor de ikke kan spille.</p>
                    </div>
                </div>
                <div class="column is-one-third">
                    <div class="box">
                        <h1 class="title is-4">3. Afbud i holdrunden</h1>
                        <p>Afbud vil automatisk blive registreret for holdrunden på de pågældende spilledatoer.</p>
                    </div>
                </div>
                <div class="column is-offset-one-quarter is-half">
                    <div>
                        <b-button type="is-info" @click="redirect" expanded>Kom igang!</b-button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>

</style>
