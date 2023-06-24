<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            FAQ
        </hero-bar>
        <section class="section is-main-section">
            <fragment v-for="faq in faqs" :key="faq.title">
                <strong>{{faq.title}}</strong>
                <p v-html="faq.text"></p>
                <hr>
            </fragment>
        </section>
    </div>
</template>

<script>
import HeroBar from "../../components/HeroBar.vue";
import TitleBar from "../../components/TitleBar.vue";

export default {
    name: "Faq",
    components: {TitleBar, HeroBar},
    data() {
        return {
            titleStack: ['Admin', 'FAQ'],
            isOpen: 0,
            faqs: [
                {
                    title: 'Hvordan tjekker jeg en opstilling?',
                    text: 'Tryk på "Holdkamp" øverest i menuen og tryk herefter på ’Opret holdkamp’. Udfyld felterne og vælg hvilken rangliste, der bruges til holdkampen.<br />'
                          + 'Tilføj alle de hold, der skal spille denne runde.<br />'
                          + 'Tilføj spillerne på holdene.<br />'
                          + 'Hvis der er fejl eller mangler i opstillingen, markeres disse med forskellige farver på spillerne'
                },
                {
                    title: 'Der er en valideringsfejl',
                    text: 'Hvis du mener, at der er en fejl i valideringen af holdkampen, så send en mail til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>, med delingslinket som findes via export funktionen øverest. Husk at uddybe fejlen.'
                },
                {
                    title: 'Der mangler en spiller i min klub',
                    text: 'nembadminton.dk henter data fra badmintonplayer.dk. Hvis spilleren ikke findes på den seneste måneds rangliste, så findes spilleren heller ikke på nembadminton.dk.<br>'
                          + 'Hvis spilleren findes på ranglisten, men stadig ikke findes på nembadminton.dk, så send en mail til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a> med oplysninger om klubben og spilleren.'
                },
                {
                    title: 'Jeg har et forslag til forbedringer eller en ny feature',
                    text: 'Send meget gerne forslag til forbedringer til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>'
                },
                {
                    title: 'Der opstår en fejl, så jeg ikke kan komme videre',
                    text: 'Tag et screenshot, lav en grundig beskrivelse og send til <a href="mailto:info@nembadminton.dk">info@nembadminton.dk</a>'
                },
                {
                    title: 'Hvad gør jeg, hvis holdene skal sættes efter forskellige ranglister?',
                    text: 'Det er vigtigt, at du er opmærksom på, at alle hold skal sættes efter samme rangliste i en given runde.<br>'
                          + 'Se reglementet for DH turneringen. Gå til <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a><br>'
                          + 'Der arbejdes i øjeblikket på en løsning, hvor der kan vælges forskellige ranglister for holdene, men det er endnu under udvikling.'
                },
                {
                    title: 'Hvornår kommer Badmintonligaen og 1. division med?',
                    text: 'Nembadminton.dk udvikles frivilligt, og det tager tid at udvikle. Derfor kan det heller ikke siges, hvornår liga og 1. division kommer med. '
                          + 'Det er muligt at tilføje hold for Badmintonligaen og 1. division, men det er ikke fuldt ud testet færdigt endnu.'
                },
                {
                    title: 'Hvilken rangliste skal jeg bruge?',
                    text: 'Se reglementet for DH-turneringen. Gå til <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a><br>'
                          + '”Den først offentliggjorte rangliste i en ny måned er gældende for holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende måned.”'
                },
                {
                    title: 'Spørgsmål til reglementet for DH-turneringen',
                    text: 'Kontakt Badminton Danmark. Gå til <a href="https://badminton.dk">https://badminton.dk</a> eller se DH-reglementet <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>'
                },
                {
                    title: 'Hvor ofte synkroniserer nembadminton.dk med badmintonplayer.dk?',
                    text: 'Hver nat opdateres ranglistepoint og nye medlemmer importeres'
                },
                {
                    title: 'Hvordan virker validering af holdopstillingen?',
                    text: 'Valideringen tager udgangspunkt i DH-reglementet (<a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>).<br>'
                          + 'Mellem holdene kontrolleres det, om der er spillere på et lavere rangerende hold, der er bedre placeret på NIVEAU-ranglisten (plus-minus buffer).<br>'
                          + 'Internt på holdet kontrolleres det, om der er spillere, der står for lavt i en kategori internt på holdet. Det gøres både i single (buffer 50 point) og double (buffer 100 point).'
                },
                {
                    title: 'Er der taget højde for U17/U19 spillere?',
                    text: 'U17/U19 spillere markeres på opstillingen og ranglistepointene for spillerne hentes, men der markeres aldrig for ulovligt hold pga. U17/U19 spillere.'
                },
                {
                    title: 'Vi har tabt en protest efter at have stillet med et hold valideret på nembadminton.dk. Hvem betaler boden?',
                    text: 'Nembadminton.dk er en service og et værktøj lavet og stillet til rådighed af frivillige. Det er derfor altid klubbens og trænerens ansvar at kontrollere, at opstillingerne er korrekte.'
                }
            ]
        }
    }

}
</script>

<style scoped>

</style>
