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
                    title: 'Jeg tror der er en holdkamps valideringens fejl?',
                    text: 'Hvis du mener der er en fejl i valideringen af holdkampen. Send en mail til <a href="mailto:danielflynygaard@gmail.com">danielflynygaard@gmail.com</a>, som indeholder et delings link via export funktionen, samt hvad du mener fejlen er'
                },
                {
                    title: 'Jeg mangler en spiller i min klub?',
                    text: 'nembadminton.dk for sin data fra badmintonplayer.dk. Hvis spilleren ikke er at findes på den seneste månedes ranglisten, vil spilleren ikke være på nembadminton.dk. Hvis spilleren findes og ikke forekommer på nembadminton.dk, send en mail til <a href="mailto:danielflynygaard@gmail.com">danielflynygaard@gmail.com</a>'
                },
                {
                    title: 'Jeg har et forslag til forbedringer eller en ny feature?',
                    text: 'Beskriv hvilket et problem du har samt et evt løsnings forslag og send det i en mail til <a href="mailto:danielflynygaard@gmail.com">danielflynygaard@gmail.com</a>'
                },
                {
                    title: 'Jeg for en fejl og kan ikke komme videre?',
                    text: 'Tag et screenshot og lav en grundig beskrivelse og send det til <a href="mailto:danielflynygaard@gmail.com">danielflynygaard@gmail.com</a>'
                },
                {
                    title: 'Hvilken en rangliste skal jeg bruge?',
                    text: 'Kig i reglementet for DH turneringen. Gå til <a href="https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer/">https://badminton.dk/om-badminton-danmark/love-regler/reglementer/holdturneringer</a>'
                },
                {
                    title: 'Spørgsmål til reglementet for DH turneringen?',
                    text: 'Kontakt Badminton Danmark. Gå til <a href="https://badminton.dk">https://badminton.dk</a>'
                },
                {
                    title: 'Hvor ofte synkroniser nembadminton.dk med badmintonplayer.dk.',
                    text: 'Hver nat opdateres rangliste pointende og nye medlemmer importers'
                }
            ]
        }
    }

}
</script>

<style scoped>

</style>
