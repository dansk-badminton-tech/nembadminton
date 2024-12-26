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
            <h1 class="title"></h1>
            <div class="columns is-multiline">
                <div class="column is-one-third">
                    <div class="box">
                        <h1 class="title is-4">1. Opsætning</h1>
                        <p>Indstil hvilke(n) klub(ber) du vil indsamle afbud fra. Samt hvilken en klub email til notifikationer som afbudene skal sendes til.</p>
                    </div>
                </div>
                <div class="column is-one-third">
                    <div class="box">
                        <h1 class="title is-4">2. Del indsamlings link</h1>
                        <p>Del indsamlings linket til alle spiller i klubben. Spillerne melder afbud på de datoer de ikke kan spille.</p>
                    </div>
                </div>
                <div class="column is-one-third">
                    <div class="box">
                        <h1 class="title is-4">3. Opret holdrunde</h1>
                        <p>Baseret på spille dato'en vil afbud automatisk blive en del af holdrunden</p>
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
