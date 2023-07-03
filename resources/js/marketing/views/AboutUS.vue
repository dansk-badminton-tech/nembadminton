<script setup>
import {faqs} from "../../admin-v2/views/faq/faqs";

const faqsArr = faqs
</script>

<template>
    <div class="section-light skills" id="about-us">
        <div class="container">
            <div class="columns is-multiline">
                <div class="column is-12 about-me">
                    <h1 class="title has-text-centered section-title">Om os</h1>
                </div>
                <div
                    class="column is-12"
                >
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
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
