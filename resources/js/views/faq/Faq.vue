<template>
    <section>
        <b-collapse
            class="card"
            animation="slide"
            v-for="(collapse, index) of collapses"
            :key="index"
            :open="isOpen == index"
            @open="isOpen = index"
            :aria-id="'contentIdForA11y5-' + index">
            <template #trigger="props">
                <div
                    class="card-header"
                    role="button"
                    :aria-controls="'contentIdForA11y5-' + index"
                    :aria-expanded="props.open"
                >
                    <p class="card-header-title">
                        {{ collapse.title }}
                    </p>
                    <a class="card-header-icon">
                        <b-icon
                            :icon="props.open ? 'menu-down' : 'menu-up'">
                        </b-icon>
                    </a>
                </div>
            </template>
            <div class="card-content">
                <div v-html="collapse.text" class="content">
                </div>
            </div>
        </b-collapse>
    </section>
</template>

<script>
export default {
    name: "Faq",
    data() {
        return {
            isOpen: 0,
            collapses: [
                {
                    title: 'Hvordan tjekker jeg en opstilling?',
                    text: 'Du går ind under holdkamp. Trykker ’Opret holdkamp’. Udfylder felter og vælger rangliste, der bruges til holdkampen.<br />'
                          + 'Tilføj alle de hold, der skal spille denne runde.<br />'
                          + 'Tilføj spillerne på holdene.<br />'
                          + 'Hvis der er fejl eller mangler markeres det både over det pågældende hold og øverste i'
                },
                {
                    title: 'Der er en validerings fejl',
                    text: 'Hvis du mener, at der er en fejl i valideringen af holdkampen, så send en mail til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>, som indeholder et delings link via export funktionen, samt en tydelig beskrivelse af fejlen.'
                },
                {
                    title: 'Der mangler en spiller i min klub',
                    text: 'nembadminton.dk henter data fra badmintonplayer.dk. Hvis spilleren ikke findes på den seneste måneds rangliste så findes spilleren heller ikke på nembadminton.dk.<br>'
                          + 'Hvis spilleren er på ranglisten, men stadig ikke findes på nembadminton.dk, send en mail til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a> med oplysninger om klubben og spilleren.'
                },
                {
                    title: 'Jeg har et forslag til forbedringer eller en ny feature',
                    text: 'Send meget gerne forslag til forbedringer til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>'
                },
                {
                    title: 'Jeg får en fejl og kan ikke komme videre',
                    text: 'Tag et screenshot, lav en grundig beskrivelse og send det til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>'
                },
                {
                    title: 'Hvilken en rangliste skal jeg bruge?',
                    text: 'Kig i reglementet for DH turneringen. Gå til <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>'
                },
                {
                    title: 'Spørgsmål til reglementet for DH turneringen?',
                    text: 'Kontakt Badminton Danmark. Gå til <a href="https://badminton.dk">https://badminton.dk</a> eller se DH-reglementet <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>'
                },
                {
                    title: 'Hvor ofte synkroniser nembadminton.dk med badmintonplayer.dk?',
                    text: 'Hver nat opdateres ranglistepoint og nye medlemmer importeres'
                },
                {
                    title: 'Hvem står bag nembadminton.dk?',
                    text: 'Daniel Fly Nygaard, programmør og badminton entusiast.'
                },
                {
                    title: 'Hvordan virker validering af holdopstillingen?',
                    text: 'Valideringen tager udgangspunkt i DH-reglementet (<a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>).<br>'
                          + 'Mellem holdene kontrolleres det om, der er spillere på et lavere rangerende hold, der er bedre placeret på NIVEAU-ranglisten (plus-minus buffer).<br>'
                          + 'Internt på holdet kontrolleres det om, der er spillere, der står for lavt i en kategori internt på holdet. Det gøres både i single (buffer 50 point) og double (buffer 100 point).'
                },
                {
                    title: 'Er der taget højde for U17/U19 spillere?',
                    text: 'U17/U19 spillere markeres på opstillingen og ranglistepointene for spillerne hentes, men der markeres aldrig for ulovligt hold pga. U17/U19 spillere.'
                },
                {
                    title: 'Vi har tabt en protest efter at have stillet med et hold valideret på nembadminton.dk. Hvem betaler boden?',
                    text: 'Nembadminton.dk er en service og et værktøj lavet og stillet til rådighed af frivillige. Det er derfor altid klubbens og trænerens ansvar at kontrollere, at opstillingerne er korrekte.'
                },
                {
                    title: 'Hvornår kommer Badmintonligaen og 1. division med?',
                    text: 'Nembadminton.dk udvikles frivilligt og det tager tid at udvikle. Derfor kan det heller ikke siges, hvornår liga og 1. division kommer med.'
                }
            ]
        }
    }

}
</script>

<style scoped>

</style>
