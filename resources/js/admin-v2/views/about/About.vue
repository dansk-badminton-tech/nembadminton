<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            Om os
        </hero-bar>
        <section class="section is-main-section">
            <strong>Hvem står bag Nembadminton.dk?</strong>
            <p>Daniel Fly Nygaard, programmør og system arkitekt med over 10 års erfaring samt badminton entusiast.
                HU formand Jesper Kamp har været behjælpelig med fortolkning og spørgsmål i forhold til DH-reglementet.
                Badminton Danmark har gjort badmintonplayer.dk data tilgængeligt</p>
            <hr>
            <strong>Visionen bag Nembadminton.dk?</strong>
            <p>Det er visionen at IT-løsninger skal gøre det nemmere at være frivillig og træner inden for badminton.
                Der er ingen kommercielle interesser bag Nembadminton.dk, og det hele drives og hostes på frivillig basis.
                Mange vil gerne bidrage og derfor lægges der med Nembadminton.dk op til, at alle kan bidrage ved at al koden ligger offentligt tilgængeligt som open source.</p>
            <hr>
            <strong>Hvordan kan man bidrage til koden?</strong>
            <p>
                Hvis man har interesse i at hjælpe med selve koden, så findes den her <a href="https://github.com/dansk-badminton-tech">Se koden her</a> .
                Alle er velkomne til at hjælpe med at forbedre og udvikle koden. Daniel Fly Nygaard står for at teste og implementere ændringer, så tag meget gerne kontakt via <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>, hvis du gerne vil hjælpe.
            </p>
            <hr>
            <strong>Er det tilladt at hente data fra Badmintonplayer.dk?</strong>
            <p>Der er lavet en databehandleraftale mellem Nembadminton.dk og Badminton Danmark, så alle nødvendige tilladelser til at bruge data er på plads for Nembadminton.dk</p>
        </section>
    </div>
</template>

<script>
import HeroBar from "../../components/HeroBar.vue";
import TitleBar from "../../components/TitleBar.vue";

export default {
    name: "About",
    components: {TitleBar, HeroBar},
    data: () => {
        return {
            titleStack: ['Admin', 'Om os'],
        }
    }
}
</script>

<style scoped>

</style>
